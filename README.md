# GamificaEdu 🚀

🌐 **Português (Brasil)** · [English](README.en.md)

> **Plataforma de Aprendizado Gamificado e Gestão de Ensino Multidisciplinar**

GamificaEdu é um ecossistema moderno voltado à educação básica e superior, construído com **Laravel 13**, **InertiaJS** e **Vue 3**. A plataforma usa elementos de gamificação para engajar alunos por meio de pontos (XP), trilhas de aprendizagem, turmas e leaderboards — com suporte a **internacionalização (pt_BR / en)**.

---

## 🛠️ Stack Tecnológica

- **Backend**: Laravel 13 (PHP 8.4+)
- **Frontend**: Vue 3 (Composition API) com InertiaJS (SPA)
- **Estilização**: TailwindCSS (tema escuro premium e responsivo)
- **Validação/DTO**: Form Requests + `spatie/laravel-data`
- **Fila/Jobs**: Laravel Queue (Banco de Dados / Redis)
- **Ambiente**: Docker via **Laravel Sail** (PHP 8.5, MySQL 8.4, Redis, Mailpit)
- **Bancos Suportados**: MySQL / PostgreSQL / SQLite
- **i18n**: arquivos `lang/` (pt_BR e en) expostos ao frontend via helper `__()`
- **Qualidade**: Pest (113 testes), PHPStan (nível 5 / Larastan), Laravel Pint, ESLint + Prettier

---

## 🏛️ Arquitetura

O backend segue um padrão sênior de responsabilidade única:

- **Controllers single-action** (`__invoke`) — uma ação por controller.
- **Form Requests** para validação e autorização.
- **Resources** para a serialização das respostas (Inertia).
- **Actions** para operações de escrita no banco (casos transacionais).
- **Services** para integrações e orquestrações (e.g. dashboards, ranking, e-mail).
- **Enums** (`UserRole`, `GeneralStatus`) para integridade de dados.
- **Models enxutas** com scopes e traits reutilizáveis (e.g. `HasRoles`).

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
- **Gestão de Matérias**: materiais (leituras), testes (quizzes) e banco de questões, além de geração automática de conteúdo (mock AI).

### 🎓 Estudante
- **Trilha de Estudos**: progresso dinâmico da disciplina.
- **Atividades Interativas**: quizzes com correção automática e XP proporcional aos acertos.
- **Leaderboard (Ranking)**: Global, por Instituição e por Matéria, com paginação, ordenação e pesquisa.

---

## 🧩 Turmas (Classes)

Camada que conecta professores, matérias e alunos:

- Uma **turma pertence a uma instituição** e tem **no máximo um professor**.
- Um **professor pode ter várias turmas**.
- Uma **turma pode ter várias matérias**.
- **Alunos são matriculados em turmas** (professor, admin ou super admin podem vincular).

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

Com Sail (prefixe `./vendor/bin/sail`) ou localmente:

```bash
# Testes (Pest)
php artisan test

# Code style (Laravel Pint)
./vendor/bin/pint            # corrige
./vendor/bin/pint --test     # apenas verifica

# Análise estática (PHPStan / Larastan, nível 5)
./vendor/bin/phpstan analyse

# Lint do frontend (ESLint + Prettier)
npm run lint                 # eslint --fix em resources/js
```

### Integração Contínua

O workflow `.github/workflows/quality.yml` executa, a cada push/PR: instalação de dependências, **Pint**, **PHPStan**, **ESLint**, build do Vite e a **suíte Pest** (PHP 8.4).

---

## 🌍 Internacionalização (i18n)

- Strings de interface ficam em `lang/pt_BR/*.php` e `lang/en/*.php` (grupos: `ui`, `nav`, `admin`, `teacher`, `superadmin`, `student`, `misc`, `classrooms`).
- O backend compartilha as traduções via Inertia; o frontend usa o helper global **`__('grupo.chave')`** (espelhando o `__()` do Laravel), com suporte a placeholders (`:name`).

---

Criado para revolucionar o engajamento educacional com as melhores práticas de desenvolvimento moderno. 🌟
