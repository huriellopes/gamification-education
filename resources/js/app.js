import '../css/app.css';
import './bootstrap';

import { __ } from '@/i18n';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Sessão/CSRF expiram após o tempo ocioso (SESSION_LIFETIME). Quando isso
// acontece, um POST do SPA volta 419 (token CSRF inválido). Em vez de estourar
// a tela de erro, recarregamos a página via GET: se o "remember" estiver ativo,
// o cookie recaller re-autentica o usuário e um novo token CSRF é emitido —
// mantendo-o logado. Sem o recaller, o GET cai naturalmente no /login.
router.on('invalid', (event) => {
    if (event.detail.response?.status === 419) {
        event.preventDefault();
        router.reload();
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Disponibiliza __() globalmente nos templates (igual ao helper do Laravel).
        app.config.globalProperties.__ = __;

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
