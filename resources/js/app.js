import '../css/app.css';
import './bootstrap';

import { installPlugins, resolve, title } from '@/inertia';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, createSSRApp, h } from 'vue';

// Sessão/CSRF expiram após o tempo ocioso (SESSION_LIFETIME). Quando isso
// acontece, um POST do SPA volta 419 (token CSRF inválido). Em vez de estourar
// a tela de erro, recarregamos a página via GET: se o "remember" estiver ativo,
// o cookie recaller re-autentica o usuário e um novo token CSRF é emitido —
// mantendo-o logado. Sem o recaller, o GET cai naturalmente no /login.
router.on('httpException', (event) => {
    if (event.detail.response?.status === 419) {
        event.preventDefault();
        router.reload();
    }
});

createInertiaApp({
    title,
    resolve,
    setup({ el, App, props, plugin }) {
        // Página renderizada pelo SSR (só visitantes) → hidrata o HTML existente;
        // caso contrário, monta do zero.
        const createVueApp = el.hasAttribute('data-server-rendered') ? createSSRApp : createApp;

        return installPlugins(
            createVueApp({ render: () => h(App, props) }),
            plugin,
        ).mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
