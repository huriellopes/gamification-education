# Arquitetura & Manutenção

Mapa das camadas, convenções e como manter o projeto. As convenções aqui não são
só recomendação: em grande parte são **verificadas por testes** (`tests/Feature/ArchTest.php`)
e pelo CI (`.github/workflows/quality.yml`).

## Stack

Laravel 13 · Inertia 2 · Vue 3 · Tailwind · Pest 4 · **PHP 8.4+** (rode via **Sail**;
o host com PHP 8.3 não executa o projeto — Symfony 8.1 exige 8.4+).

## Fluxo de uma requisição

```
routes/parts/{super_admin,admin,teacher,student,shared}.php   ← rotas por papel
        │  middleware role.*        (fronteira de acesso declarativa — 1ª camada)
        ▼
Controller (__invoke)               ← só orquestra: resolve deps, autoriza, delega
        ├── FormRequest             validação + autorização de entrada
        ├── Policy                  autorização por registro (2ª camada)
        ├── Action                  ESCRITA / efeitos colaterais
        └── Service                 LEITURA / montagem / orquestração
                ▼
        Model + Traits + Enums
                ▼
        Resource                    transformação para o frontend
                ▼
        Inertia → Vue (Pages por papel + Components)
```

Transversais: Observers, Jobs, Mail, Listeners/Events, i18n (`lang/pt_BR` + `lang/en`).

## Onde coloco cada coisa?

| Preciso de… | Vai em | Regra |
|---|---|---|
| Uma rota nova | `routes/parts/<papel>.php` | dentro do grupo `role.*` correspondente |
| Reagir a uma requisição | `app/Http/Controllers/.../XyzController.php` | **um `__invoke` por controller**; sem query/regra inline |
| Validar entrada | `app/Http/Requests` | estende `FormRequest` |
| Autorizar acesso a um registro | `app/Policies` | sufixo `Policy`; escopo multi-instituição via `ScopesToInstitution` |
| **Escrever** algo (criar/alterar/efeito) | `app/Actions` | classe focada num caso de uso |
| **Ler**/montar/orquestrar dados | `app/Services` | **sem** `Request`/`Inertia` (agnóstico a HTTP) |
| Transformar modelo → payload | `app/Http/Resources` | |
| Valor de domínio fechado | `app/Enums` | enum PHP nativo |
| Comportamento reutilizável de model/policy/service | `app/Traits` ou `.../Concerns` | ex.: `BuildsHealthReport`, `BuildsDailyChart` |

Princípio-guia: **o controller não faz trabalho** — ele delega. Query/montagem →
Service; escrita/efeito → Action; validação → Request; autorização → Policy.

## Convenções travadas por teste (`ArchTest`)

- Todo o `app/` usa `declare(strict_types=1)`.
- Nada de `dd/dump/ray/var_dump/die` em `app/`.
- Controllers são de ação única (`__invoke`).
- `App\Services` e `App\Actions` não dependem de `Illuminate\Http\Request` nem de `Inertia`.
- `FormRequests` estendem a base; `Policies` têm sufixo `Policy`; `Enums` são enums.

Rodar: `sail pest tests/Feature/ArchTest.php`.

## i18n

- Toda chave existe em **`pt_BR` e `en`** — `tests/Feature/LocaleParityTest.php`
  falha se divergirem.
- Grupos expostos ao frontend: ver `HandleInertiaRequests::translations()`.
  O helper `__()` (`resources/js/i18n.js`) resolve por dot-notation; chave ausente
  cai no fallback exibindo a própria chave.
- Nomes de campos de validação: `lang/<locale>/validation.php` → `attributes`.

## Qualidade

`composer quality` roda, na ordem: **Rector** (refactor) → **Pint** (formatação)
→ **PHPStan** (estática, nível 5) → **ESLint** (Vue) → **Pest**. O CI
(`CI - Code Integrity`) roda o mesmo conjunto e é *required check* em `dev`/`main`.

Comandos úteis (via Sail):

```bash
sail up -d                      # sobe o ambiente (PHP 8.5)
sail pest                       # testes
sail pest --coverage            # cobertura
sail bin pint                   # formata
sail php vendor/bin/rector process   # aplica refactors
sail npm run dev                # front em watch
```

## Decisões registradas (ADRs)

- [`docs/adr/0001-dashboards-por-papel.md`](adr/0001-dashboards-por-papel.md) — dashboards com rota por papel (não unificar).
- [`docs/adr/0002-2fa-e-viabilidade-fortify.md`](adr/0002-2fa-e-viabilidade-fortify.md) — 2FA custom endurecido + viabilidade do Fortify.

## Deduplicação por papel (feito)

- **`ClassroomResource`**: 3 cópias (Admin/SuperAdmin/Teacher) → **1 único**
  (`App\Http\Resources\ClassroomResource`). Campos específicos de papel só
  aparecem quando o controller carrega/conta a relação (`whenLoaded`/`whenCounted`).
- **FormRequests de matéria e turma**: regras comuns extraídas para traits
  `App\Http\Requests\Concerns\{SubjectRules,ClassroomRules}`; cada request mantém
  só o seu `authorize()`. Request legado `Http\Requests\StoreSubjectRequest`
  (não usado) removido.
- **`SubjectResource` (mantido separado de propósito):** a versão do Student é
  **enxuta por segurança** — não inclui provas/gabarito (`correct_option_index`).
  Unificar com a versão rica (Super Admin/Admin/Professor) arriscaria vazar o
  gabarito para o aluno. **Não** consolidar.

## Dívidas conhecidas / próximos passos

- **Actions**: método de entrada misto (`__invoke` vs `execute`) — padronizar.
