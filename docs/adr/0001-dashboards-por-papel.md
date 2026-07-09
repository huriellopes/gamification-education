# ADR 0001 — Dashboards com rota por papel (não unificar em `/dashboard`)

- **Status:** Aceito
- **Data:** 2026-07-08
- **Contexto de decisão:** discussão sobre unificar os quatro dashboards numa única rota/tela.

## Contexto

A aplicação expõe quatro dashboards, um por papel de usuário:

| Papel | Endpoint | Nome da rota | Controller |
|---|---|---|---|
| Super Admin | `/super-admin/dashboard` | `super-admin.dashboard` | `SuperAdmin\SuperAdminDashboardController` |
| Admin | `/admin/dashboard` | `admin.dashboard` | `Admin\AdminDashboardController` |
| Professor | `/teacher/dashboard` | `teacher.dashboard` | `Teacher\TeacherDashboardController` |
| Aluno | `/student/dashboard` | `student.dashboard` | `Student\StudentDashboardController` |

Cada rota vive em um grupo de middleware `role.*` (aliases em `bootstrap/app.php`).
`GET /dashboard` (`DashboardRedirectController`) redireciona o usuário logado para o
dashboard do seu papel.

Surgiu a proposta de "unificar os dashboards". O termo é ambíguo e cobre duas
coisas distintas:

1. **Unificar a implementação** — reaproveitar componentes/serviços entre os
   dashboards.
2. **Unificar o roteamento/tela** — uma única rota `/dashboard` renderizando
   seções condicionais por papel (`v-if="role === ..."`).

## Decisão

- Adotar (1): extrair componentes e serviços compartilhados
  (`LineChart.vue`, `MetricCard.vue`, `SystemHealthPanel.vue`, os
  `*DashboardService`), mantendo as quatro páginas por papel.
- **Rejeitar (2):** não colapsar as quatro rotas numa só.

## Justificativa

A separação por rota + middleware `role.*` é a fronteira de autorização mais
barata e auditável do sistema (primeira camada de defesa; as policies por
registro são a segunda). Unificar a rota moveria a filtragem de acesso de uma
borda **declarativa** (middleware) para condicionais **imperativas** no
controller e no template — mais fácil de errar e mais difícil de auditar.

Hoje a fronteira é declarada uma vez e barra o papel errado antes de qualquer
código do controller rodar:

```php
// bootstrap/app.php — aliases de papel
$middleware->alias([
    'role.super_admin' => EnsureUserIsSuperAdmin::class,
    'role.admin'       => EnsureUserIsAdmin::class,
    'role.teacher'     => EnsureUserIsTeacher::class,
    'role.student'     => EnsureUserIsStudent::class,
]);
```

```php
// routes/parts/super_admin.php — o grupo inteiro fica atrás do middleware
Route::middleware(['auth', 'role.super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
        // ... demais rotas do super admin
    });
```

Numa rota única, essa checagem viraria `if ($user->role === ...)` no controller
e `v-if` no template — repetida e sujeita a esquecimento.

Trade-off resumido:

| Dimensão | Rota por papel (escolhido) | Rota única (rejeitado) |
|---|---|---|
| Fronteira de acesso | Middleware barra o papel errado antes de qualquer código | Decisão vira `if/switch` + `v-if`, imperativa |
| Vazamento de dados | Cada service só conhece o escopo de 1 papel | Ou vira um `switch` gigante (sem ganho), ou envia dados de todos os papéis ao browser |
| Bundle | Inertia faz code-split por página | Mega-componente com a UI de todos os papéis para todos |
| Manutenção | 4 arquivos focados e isolados | God-component com condicionais de papel |
| Testes | 1 rota por papel; middleware barra o resto | Cada teste precisa provar que as seções dos outros papéis não aparecem |
| Blast radius | — | Alto: 4 rotas, redirect, middleware, breadcrumbs, todos os `route('...dashboard')`, testes |

O ganho legítimo de "menos duplicação" foi capturado na camada de componentes,
sem os custos de segurança de fundir rotas. O "endpoint único" desejado para UX
já existe via `/dashboard` (redirect).

## Quando reavaliar

Rever esta decisão apenas se os papéis convergirem para **o mesmo conjunto de
dados e capacidades**, diferenciando por dado e não por permissão (ex.: um SaaS
onde todos veem "meu painel" com as mesmas seções). Não é o caso atual: super
admin vê saúde do sistema/jobs/instituições; aluno vê progresso/ranking.

## Consequências

- Novos dashboards ou widgets devem reutilizar os componentes/serviços
  compartilhados, não duplicar SVG/consultas.
- Reduzir boilerplate no futuro = extrair mais componentes, **nunca** fundir
  rotas.
