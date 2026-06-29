# GamificaEdu 🚀

> **Plataforma de Aprendizado Gamificado e Gestão de Ensino Multidisciplinar**

GamificaEdu é um ecossistema moderno voltado à educação básica e superior, construído com **Laravel 11**, **InertiaJS** e **Vue 3**. A plataforma utiliza elementos avançados de gamificação para engajar alunos por meio de pontos (XP), trilhas de aprendizagem visuais, conquistas e leaderboards.

---

## 🛠️ Stack Tecnológica

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API) com InertiaJS (Single Page Application)
- **Estilização**: TailwindCSS & Custom Vanilla CSS para um visual escuro premium e responsivo
- **Fila/Jobs**: Laravel Queue (Banco de Dados / Redis)
- **Bancos Suportados**: PostgreSQL / MySQL / SQLite
- **Testes**: PestPHP (Suíte abrangente com >95 testes)

---

## 🌟 Principais Funcionalidades

### 👑 Super Administrador
- **Multi-instituições**: Criação, edição e exclusão de escolas/universidades (com validação real de CNPJ).
- **Gerenciamento de Administradores**: Vinculação de administradores a múltiplas instituições gerenciadas.
- **Log Pruning em Fila**: Comando e botão visual com indicador reativo para limpar arquivos de log mantendo apenas os últimos 3 dias, processado em segundo plano por fila.
- **Métricas de Acesso**: Captura de tráfego do site público com endereços IP criptografados de ponta a ponta no banco de dados, sendo descriptografados dinamicamente apenas na visualização administrativa.
- **Fila e Jobs Falhos**: Visualização em tempo real de jobs com falha, com suporte para reprocessar (retry) ou apagar os registros da fila.
- **Impersonificação**: Permite que o Super Admin acesse temporariamente a plataforma sob a identidade de qualquer usuário para suporte técnico.

### 🏫 Administrador da Instituição
- **Alternância de Contexto**: Admins com acesso a múltiplas instituições podem alternar o contexto ativo instantaneamente pelo cabeçalho.
- **Gestão de Professores & Alunos**: CRUD completo com busca reativa, ordenação e filtros.
- **Associação de Matérias**: Cadastro de disciplinas com vinculação múltipla de professores (sem duplicidades).

### 👨‍🏫 Professor
- **Criador de Conteúdo**: Geração automática de materiais de estudo e questionários simulados (mock AI).
- **Editor de Atividades**: Criação de materiais (artigos/leituras), testes (quizzes) e banco de questões associadas.
- **Desempenho da Turma**: Painel com estatísticas de progresso e XP acumulado de cada estudante.

### 🎓 Estudante
- **Trilha de Estudos**: Visualizador dinâmico de progresso da disciplina.
- **Atividades Interativas**: Realização de quizzes com correção automática e atribuição de XP proporcional aos acertos.
- **Leaderboard (Ranking)**: Rankings reativos (Global, por Instituição e por Matéria) com paginação, ordenação e pesquisa por nome ou instituição.

---

## 🔑 Recursos Avançados de Autenticação

- **Magic Login Token**: Acesso rápido via link assinado único enviado ao e-mail.
- **Remember Me**: Opção integrada no magic link para manter a sessão ativa permanentemente no dispositivo.
- **Controle de Sessão Única**: Conectar-se em um novo navegador desconecta automaticamente sessões anteriores ativas para segurança da conta.
- **Forçar Alteração de Senha**: Contas recém-criadas pelos administradores são forçadas a redefinir sua senha no primeiro acesso.

---

## ⚡ Instalação e Execução Local

1. **Clonar o Repositório e Instalar Dependências PHP**:
   ```bash
   composer install
   ```

2. **Instalar Dependências Node**:
   ```bash
   npm install
   ```

3. **Configurar o Ambiente**:
   Copie o arquivo `.env.example` para `.env` e configure suas credenciais de banco de dados e servidor de e-mail.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Rodar Migrações e Seeder**:
   O seed gera aproximadamente 150 registros completos (usuários, instituições, matérias, tentativas) para testes de paginação:
   ```bash
   php artisan migrate --seed
   ```

5. **Iniciar a Fila de Background (Obrigatório para e-mails e logs)**:
   ```bash
   php artisan queue:work
   ```

6. **Iniciar os Servidores Locais**:
   ```bash
   php artisan serve
   # Em outro terminal:
   npm run dev
   ```

---

## 🧪 Suíte de Testes e Qualidade

- **Executar Testes de Unidade e Integração (Pest)**:
  ```bash
  php artisan test
  ```
- **Executar Formatação Automática (Laravel Pint)**:
  ```bash
  ./vendor/bin/pint
  ```
- **Executar Análise Estática de Código (PHPStan)**:
  ```bash
  ./vendor/bin/phpstan analyse
  ```

---
Criado para revolucionar o engajamento educacional com as melhores práticas de desenvolvimento moderno. 🌟
