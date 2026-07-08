# ADR 0002 — Revisão do 2FA atual e viabilidade do Laravel Fortify

- **Status:** Aceito — **Caminho B implementado** (Fortify avaliado e adiado)
- **Data:** 2026-07-08

> **Decisão:** optou-se pelo **Caminho B** (endurecer o 2FA custom). F1–F4 foram
> corrigidos (ver "Registro de implementação" no fim). Migrar para o Fortify
> (Caminho A) permanece viável e recomendado como épico de auth futuro.

## Contexto

O 2FA hoje é uma implementação **artesanal** sobre `pragmarx/google2fa`
(+ `bacon/bacon-qr-code` para o QR). Componentes:

- **Colunas** (`2026_07_07_100000_add_two_factor_columns_to_users_table`):
  `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`
  — cast como `encrypted` / `encrypted:array` / `datetime` no `User`.
- **Serviço:** `App\Services\Auth\TwoFactorAuthenticationService`
  (gera secret, verifica TOTP, monta otpauth/QR SVG, gera recovery codes).
- **Login:** `StoreAuthenticatedSessionController` desloga e desvia para o
  desafio quando `hasTwoFactorEnabled()` (ou seja, `two_factor_confirmed_at`).
- **Desafio:** `TwoFactorChallengeController` (tela) +
  `VerifyTwoFactorChallengeController` (valida TOTP ou recovery code).
- **Gestão no perfil:** `Enable`, `Confirm`, `Disable`,
  `RegenerateRecoveryCodes`.

## Observação central

Essa implementação é, na prática, **uma reimplementação do 2FA do Fortify**:
mesmas libs subjacentes (`google2fa` + `bacon-qr-code`), **mesmas três colunas**
e mesmo fluxo (enable → confirm → challenge → recovery). Isso torna o Fortify
altamente compatível — quase drop-in para a parte de 2FA.

## Achados da revisão (o que o código atual faz bem e onde falha)

### Pontos fortes
- Secret e recovery codes **criptografados em repouso** (`encrypted*`).
- Recovery codes gerados com CSPRNG (`random_bytes`); consumo remove o código
  usado (`replaceRecoveryCode` com `hash_equals`).
- Distinção correta entre **configurado** e **confirmado**: o login só exige 2FA
  após `two_factor_confirmed_at` — não tranca o usuário no meio do setup.
- Regenera a sessão no login e no desafio (mitiga fixation).

### Lacunas (por severidade)

| # | Severidade | Achado | Detalhe |
|---|---|---|---|
| F1 | 🔴 Alta | **Sem rate limiting no desafio 2FA** | `POST /two-factor-challenge` (`two-factor.login.store`) não tem middleware `throttle`. Permite força-bruta do TOTP de 6 dígitos e, pior, dos **recovery codes**. O Fortify aplica throttle por padrão. |
| F2 | 🟡 Média | **Enable/Disable sem confirmação de senha** | As rotas `two-factor.enable/disable` (em `routes/parts/shared.php`) não passam por `password.confirm`. Sessão sequestrada consegue ativar/desativar 2FA sem reautenticar. Fortify gateia isso. |
| F3 | 🟡 Média | **Eviction de sessão só no caminho sem 2FA** | O login por senha remove as outras sessões do usuário; o caminho que finaliza via 2FA (`VerifyTwoFactorChallengeController`) **não** faz o mesmo. Inconsistência de "sessão única". |
| F4 | 🟢 Baixa | Comparação de recovery code no desafio via `in_array(..., true)` (não constant-time) | Impacto marginal (o segredo real é o TOTP), mas `hash_equals` seria mais correto. |

**F1 deve ser corrigido independentemente da decisão Fortify** — é a única
falha explorável remotamente.

## Fortify: o que ele entrega

| Recurso | Custom hoje | Fortify |
|---|---|---|
| Colunas `two_factor_*` | ✅ (idênticas) | ✅ (mesmo schema) |
| TOTP via `google2fa` | ✅ | ✅ |
| QR via `bacon-qr-code` | ✅ | ✅ |
| Recovery codes | ✅ | ✅ |
| **Throttle no desafio** | ❌ (F1) | ✅ nativo |
| **`password.confirm` no enable/disable** | ❌ (F2) | ✅ nativo |
| Headless (Inertia/Vue) | ✅ | ✅ (Fortify é headless; você fornece as telas) |
| Manutenção/segurança | por nossa conta | mantido pela Laravel |

## Viabilidade: **sim, é viável** — com uma ressalva de escopo

A ressalva não é o 2FA (esse encaixa quase perfeitamente), e sim que o **login
desta app é bastante customizado**: magic-login, `force-change-password`, papéis
e eviction de sessão, tudo em controllers próprios. O desafio 2FA do Fortify é
acoplado ao **pipeline de login do Fortify** (`RedirectIfTwoFactorAuthenticatable`).
Para herdar o throttle + o fluxo de desafio do Fortify, adota-se também a ação de
login do Fortify, substituindo o `StoreAuthenticatedSessionController` e
re-encaixando as customizações como *actions*/pipeline do Fortify.

### Caminho A — Adotar Fortify (2FA + login)
- **Prós:** terceiriza manutenção e segurança; ganha F1 e F2 de graça; schema já
  compatível (migração possivelmente sem alterações); menos código nosso.
- **Contras:** reintegrar magic-login, force-change-password e eviction como
  customizações do Fortify; maior blast radius no fluxo de autenticação; as
  telas Inertia de login/desafio precisam falar com as rotas do Fortify.
- **Esforço:** médio. **Risco:** médio (fluxo de auth central).

### Caminho B — Manter custom e endurecer
- **Prós:** baixo risco; mantém controle total dos fluxos sob medida (magic link
  etc.); resolve F1–F3 pontualmente.
- **Ações:**
  1. `throttle:5,1` (ou `throttle` nomeado) em `two-factor.login.store` **(F1)**.
  2. `password.confirm` nas rotas `two-factor.enable`/`disable` **(F2)**.
  3. Replicar a eviction de sessão no `VerifyTwoFactorChallengeController` **(F3)**.
  4. `hash_equals`/comparação normalizada no match de recovery code **(F4)**.
- **Esforço:** baixo. **Risco:** baixo.

## Recomendação

Duas ações em horizontes diferentes:

1. **Imediato, independentemente da decisão:** aplicar F1 (throttle no desafio).
   É a única lacuna explorável remotamente. Barato e reversível.
2. **Estratégico:** como o 2FA já espelha o Fortify mas o **login** é fortemente
   customizado, o **Caminho B** (endurecer o custom) tem a melhor relação
   custo/risco *agora*. Migrar para o **Caminho A** faz sentido quando o objetivo
   for reduzir manutenção/padronizar a autenticação — idealmente num épico de
   auth dedicado, não acoplado a outra entrega.

## Consequências

- Se B: F1–F4 viram tarefas de hardening; manter testes de 2FA cobrindo throttle
  e password-confirm.
- Se A: planejar migração de login (magic-link, force-change-password, eviction)
  como customizações do Fortify e ajustar as telas Inertia; validar que a
  migração das colunas é compatível antes de publicar `config/fortify.php`.

## Registro de implementação (Caminho B — 2026-07-08)

| Achado | Correção | Local |
|---|---|---|
| F1 | `throttle:5,1` no desafio 2FA | `routes/auth.php` (`two-factor.login.store`) |
| F2 | Senha atual exigida para **desativar** (só quando ativo) e **regenerar**; cancelar setup não confirmado dispensa senha | `DisableTwoFactorController`, `RegenerateRecoveryCodesController` + campo inline em `TwoFactorAuthenticationForm.vue` |
| F3 | Eviction de sessão única após login via 2FA | `VerifyTwoFactorChallengeController` |
| F4 | Match de recovery code com `hash_equals` (constant-time) | `VerifyTwoFactorChallengeController` |

Escopo do F2 é proporcional: o *enable* segue em um clique (é auto-protetivo); a
senha só é pedida nas ações que enfraquecem/rotacionam um 2FA já ativo.

Cobertura: `tests/Feature/Auth/TwoFactorAuthenticationTest.php` (12 testes,
incluindo throttle, exigência de senha e cancelamento sem senha).
