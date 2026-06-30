# GamificaEdu 🚀

🌐 **Português (Brasil)** · [English](README.en.md)

> **Plataforma de Aprendizado Gamificado e Gestão de Ensino Multidisciplinar**

GamificaEdu é um ecossistema moderno voltado à educação básica e superior, construído com **Laravel 13**, **InertiaJS** e **Vue 3**. A plataforma usa elementos de gamificação para engajar alunos por meio de pontos (XP), trilhas de aprendizagem, turmas e leaderboards — com suporte a **internacionalização (pt_BR / en)**.

---

## 🛠️ Stack Tecnológica

- **Backend**: Laravel 13 (PHP 8.4+)
- **Frontend**: Vue 3 (Composition API) com InertiaJS (SPA)
- **Estilização**: TailwindCSS (tema escuro premium, determinístico e responsivo)
- **Validação**: Form Requests (validação + autorização) e JsonResources (serialização)
- **Fila/Jobs**: Laravel Queue (Banco de Dados / Redis)
- **Ambiente**: Docker via **Laravel Sail** (PHP 8.5, MySQL 8.4, Redis, Mailpit)
- **Bancos Suportados**: MySQL / PostgreSQL / SQLite
- **i18n**: arquivos `lang/` (pt_BR e en) expostos ao frontend via helper `__()`
- **Qualidade**: Pest (116 testes), PHPStan (nível 5 / Larastan), Laravel Pint, Rector e ESLint + Prettier — orquestrados via `composer quality`

---

## 🏛️ Arquitetura

O backend segue um padrão sênior de responsabilidade única:

- **Controllers single-action** (`__invoke`) — uma ação por controller, sem queries (apenas regra de negócio).
- **Form Requests** para validação e autorização.
- **Resources** para a serialização das respostas (Inertia).
- **Actions** para operações de escrita no banco (casos transacionais).
- **Services** para integrações e orquestrações (e.g. dashboards, ranking, e-mail).
- **Policies** para autorização por modelo (auto-descobertas).
- **Observers** para regras de ciclo de vida (e.g. geração de slug único de matéria/instituição).
- **Events & Listeners** para efeitos colaterais desacoplados (e.g. `MilestoneReached`, atualização de último login).
- **Enums** string-backed (`UserRole`, `GeneralStatus`, `ScoreSource`, `ReportStatus`, `SupportStatus`).
- **Models enxutas** com scopes e traits reutilizáveis (`HasRoles`, `Activatable`, `BelongsToInstitution`).

---

## 🌟 Principais Funcionalidades

### 👑 Super Administrador
- **Multi-instituições**: CRUD de escolas/universidades (com validação de CNPJ).
- **Gestão global**: usuários, matérias e **turmas** de todas as instituições.
- **Vínculo aluno↔turma**: ao cadastrar/editar um aluno, pode vinculá-lo a uma turma.
- **Log Pruning em fila**, **Métricas de Acesso** (IP criptografado), **Jobs Falhos** (retry/delete) e **Impersonificação** para suporte.

### 🏫 Administrador da Instituição
- **Alternância de contexto** entre instituições gerenciadas.
- **Gestão de Professores & Alunos** (CRUD com busca reativa por nome, e-mail e turma).
- **Matérias** com vínculo de professores.
- **Turmas**: cria turmas da instituição, vincula **1 professor** e várias **matérias**, e matricula alunos.

### 👨‍🏫 Professor
- **Sidebar dedicada**: Minhas Turmas, Meus Alunos e Matérias.
- **Dashboard com métricas reais e reativas** (turmas, alunos, matérias) + **gráficos de desempenho** (por turma e por aluno), com auto-atualização.
- **Cadastro de alunos** e vínculo a uma de suas turmas.
- **Gestão de Matérias**: vincula a matéria a uma de suas turmas, gerencia materiais (leituras), testes (quizzes) e banco de questões — com validação de integridade (índice da resposta correta dentro das opções) e geração automática de conteúdo idempotente (mock AI).

### 🎓 Estudante
- **Trilha de Estudos**: matérias das turmas em que está matriculado, com progresso dinâmico.
- **Atividades Interativas**: quizzes com correção automática e XP proporcional aos acertos.
- **Leaderboard (Ranking)**: Global, por Instituição e por Matéria, com paginação, ordenação e pesquisa.

### 🌐 Site Público
- Landing page com métricas reais da plataforma e SEO (sitemap + Schema.org).
- **Páginas legais**: **Política de Privacidade** (LGPD) e **Diretrizes de Uso** (bilíngues).
- **Banner de consentimento de cookies** com links para as páginas legais.

---

## 🧩 Turmas (Classes)

Camada que conecta professores, matérias e alunos:

- Uma **turma pertence a uma instituição** e tem **no máximo um professor**.
- Um **professor pode ter várias turmas**.
- Uma **turma pode ter várias matérias**.
- **Alunos são matriculados em turmas** (professor, admin ou super admin podem vincular).
- O professor responsável pela turma recebe automaticamente acesso ao conteúdo das matérias dela.

---

## 🔑 Recursos Avançados de Autenticação

- **Magic Login Token**: acesso via link assinado único enviado ao e-mail (uso único, expira em 15 min).
- **Remember Me** integrado ao fluxo de login.
- **Controle de Sessão Única**: novo login encerra sessões anteriores.
- **Forçar Alteração de Senha** no primeiro acesso de contas criadas por administradores.

---

## ⚡ Instalação e Execução (Laravel Sail — recomendado)

O projeto roda em contêineres Docker via Laravel Sail.

```bash
# 1. Variáveis de ambiente
cp .env.example .env

# 2. Instalar dependências PHP (via contêiner efêmero)
docker run --rm -v $(pwd):/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install

# 3. Subir os contêineres (app, mysql, redis, mailpit)
./vendor/bin/sail up -d

# 4. App key, migrações e seed de demonstração
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 5. Dependências e build do frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

> A fila roda via `QUEUE_CONNECTION` (database/redis). Para processar e-mails e jobs em segundo plano:
> ```bash
> ./vendor/bin/sail artisan queue:work
> ```

### Alternativa sem Sail (PHP 8.4+ local)

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan queue:work      # em um terminal
php artisan serve           # em outro
npm run dev                 # em outro
```

---

## 🧪 Qualidade e Testes

O `composer quality` executa toda a esteira na ordem: **Rector → Pint → PHPStan → ESLint → Pest**.

```bash
# Esteira completa (prefixe ./vendor/bin/sail ao usar o Sail)
composer quality

# Comandos individuais
composer pest            # Testes (Pest) — 116 testes
composer pint            # Code style (Laravel Pint)
composer phpstan         # Análise estática (PHPStan / Larastan, nível 5)
composer rector          # Refatorações automáticas (Rector)
composer rector:check    # Rector em modo dry-run
composer lint:vue        # Lint do frontend (ESLint + Prettier)
```

### Integração Contínua

- `.github/workflows/quality.yml` — a cada push/PR: instalação de dependências, **Rector (check)**, **Pint**, **PHPStan**, **ESLint**, build do Vite e a **suíte Pest** (PHP 8.4).
- `.github/workflows/release.yml` — após o CI passar na `main`, **incrementa a versão**, grava o arquivo `VERSION`, cria a **tag `vX.Y.Z`** e a **GitHub Release** com notas geradas automaticamente.

---

## 🏷️ Versionamento e Releases

- A versão única da aplicação fica no arquivo **`VERSION`** na raiz (atualmente `0.1.0`).
- A versão é **exposta em todos os dashboards** (via `HandleInertiaRequests`) e atualizada automaticamente pelo workflow de release.
- Bump de _patch_ é automático; um _minor/major_ manual no arquivo `VERSION` é respeitado pelo pipeline.

---

## 🌍 Internacionalização (i18n)

- Strings de interface ficam em `lang/pt_BR/*.php` e `lang/en/*.php`. Os grupos compartilhados com o frontend via Inertia são: `ui`, `nav`, `admin`, `teacher`, `superadmin`, `student`, `misc` e `classrooms`.
- O frontend usa o helper global **`__('grupo.chave')`** (espelhando o `__()` do Laravel), com suporte a placeholders (`:name`).
- Conteúdos extensos (e.g. páginas legais em `lang/{locale}/legal.php`) são entregues como _props_ da página — não trafegam no payload global de traduções.

---

Criado para revolucionar o engajamento educacional com as melhores práticas de desenvolvimento moderno. 🌟
