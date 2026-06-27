# Walkthrough - Plataforma de Gamificação Educacional

Concluímos com sucesso a implementação da base da nossa plataforma de gamificação educacional usando **Laravel 13**, **InertiaJS**, **Vue 3** e **Tailwind CSS**. A estrutura do projeto adota padrões modernos de arquitetura, garantindo a separação de responsabilidades (SRP).

---

## O que foi construído

### 1. Banco de Dados e Models (Eloquent)
* **`Institution`**: Representa a instituição de ensino à qual matérias e alunos pertencem.
* **`Subject`**: Matérias pertencentes a instituições.
* **`StudyMaterial`**: Materiais didáticos contendo conteúdo textual. Concede +15 XP ao aluno quando lido.
* **`Test` & `Question`**: Atividades/Quizzes compostos de perguntas de múltipla escolha. Concede até +50 XP ao aluno.
* **`TestAttempt`**: Registros de histórico de tentativas de teste, pontuações e percentual de acertos.
* **`ScoreHistory`**: Extrato de pontuações XP do aluno (por quê e quando ganhou pontos).
* **`User`**: Atualizado com relações de perfil (`role`), pontos XP e instituição.

### 2. Actions (Responsabilidade Única)
Localizadas em `app/Actions`:
* **`AddPointsToUserAction`**: Orquestra a adição segura de pontos ao saldo do usuário e registra no extrato `ScoreHistory` em uma transação de banco de dados.
* **`GenerateStudyMaterialAction`**: Instancia um novo material didático.
* **`GenerateTestForSubjectAction`**: Instancia um teste com suas respectivas questões.
* **`CompleteStudyMaterialAction`**: Marca um material como lido pelo aluno (garantindo que não ganhe pontos duas vezes pelo mesmo material).
* **`SubmitTestAttemptAction`**: Corrige o teste enviado pelo aluno, calcula a pontuação e implementa uma regra de gamificação avançada: **o aluno pode tentar várias vezes, mas só ganha pontos adicionais caso supere a sua melhor nota anterior** (recebendo apenas a diferença de pontos).

### 3. Services (Mecanismos Auxiliares)
Localizados em `app/Services`:
* **`MaterialGenerationService`**: Contém textos e questionários reais e estruturados em português para os temas *Laravel Eloquent*, *Vue Composition API*, *Tailwind CSS Layouts* e um gerador dinâmico fallback para qualquer outro tema digitado pelo admin.
* **`RankingService`**: Gera coleções para painéis de classificação geral, por instituição de ensino e por matéria específica.

### 4. Middleware de Autorização de Acesso (RBAC)
Criados e registrados em `bootstrap/app.php` para segurança:
* **`EnsureUserIsAdmin` (`role.admin`)**: Apenas administradores acessam rotas `/admin`.
* **`EnsureUserIsStudent` (`role.student`)**: Alunos acessam rotas `/student` e redireciona administradores de volta ao painel administrativo.

### 5. Frontend Premium (Inertia + Vue 3 + Tailwind CSS)
Construído com design moderno dark-mode, gradientes e micro-animações:
* **`Admin/Dashboard.vue`**: Métricas gerais e tabela de classificação de estudantes com busca.
* **`Admin/Subjects/Show.vue`**: Detalhes da matéria, listagem de materiais e testes gerados, além da **Interface do Gerador de IA** onde o administrador digita um tema e cria conteúdos instantaneamente.
* **`Student/Dashboard.vue`**: Círculo de nível do aluno, barra de progresso para subir de nível (XP), matérias da sua instituição (com progresso em porcentagem) e widget lateral com top competidores.
* **`Student/Subjects/Show.vue`**: Timeline interativa da jornada de aprendizado do estudante para aquela matéria.
* **`Student/Materials/Show.vue`**: Leitor de materiais com barra de ação flutuante animada para concluir leitura e resgatar pontos.
* **`Student/Tests/Show.vue`**: Interface de teste de múltipla escolha interativa e responsiva.
* **`Ranking/Index.vue`**: Página de ranking completa com pódio destacado para os top 3 alunos (🥇 🥈 🥉) e abas de filtros.

---

## Verificação e Qualidade

### Testes Automatizados
Escrevemos um conjunto de testes em `tests/Feature/GamificationTest.php` e rodamos o conjunto padrão do Laravel. **Todos os 28 testes passaram com sucesso (76 asserções)**:
* Validação de adição de pontos sem duplicidade ao ler material.
* Correção de atividades e lógica de concessão de diferença de pontos na melhoria de nota.
* Ordenação correta do serviço de ranking.
* Autenticação e perfilamento do Laravel Breeze.

---

## Como Rodar e Testar Localmente via Laravel Sail

Siga as instruções abaixo para subir o projeto usando o Laravel Sail.

### 1. Iniciar os Containers do Sail
```bash
# Sobe o Laravel Sail em background (na porta 80 por padrão)
./vendor/bin/sail up -d
```

### 2. Rodar as Migrações e Seeders
Como o banco de dados agora está rodando no contêiner do MySQL, precisamos migrar e popular os dados dentro do contêiner:
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 3. Iniciar o Vite
```bash
# Executa o Vite dentro do container do Sail
./vendor/bin/sail npm run dev
```

### 4. Acessar e Logar
Acesse `http://localhost` no seu navegador e logue usando as credenciais abaixo:

#### Perfil Administrador (Gerador de Matérias/Testes)
* **E-mail**: `admin@example.com`
* **Senha**: `password`

#### Perfil Estudante (Lê Materiais, Faz Testes e Disputa Ranks)
* **E-mail**: `student@example.com`
* **Senha**: `password`

*Outros estudantes já populados no ranking para disputar pódio:*
* `maria@example.com` (290 XP)
* `joao@example.com` (180 XP)
* `pedro@example.com` (95 XP)
