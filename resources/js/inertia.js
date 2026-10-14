import { __ } from '@/i18n';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Configuração compartilhada entre o bundle do navegador (app.js) e o do
// servidor (ssr.js), para os dois renderizarem exatamente a mesma árvore.

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

export const title = (title) => `${title} - ${appName}`;

export const resolve = (name) =>
    resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue'),
    );

/**
 * Registra os plugins globais no app Vue.
 *
 * @param {import('vue').App} app
 * @param {import('vue').Plugin} plugin  plugin do Inertia
 * @param {object} [ziggyConfig]  rotas do Ziggy (obrigatório no SSR, onde não há window.Ziggy)
 */
export function installPlugins(app, plugin, ziggyConfig) {
    app.use(plugin).use(ZiggyVue, ziggyConfig);

    // Disponibiliza __() globalmente nos templates (igual ao helper do Laravel).
    app.config.globalProperties.__ = __;

    return app;
}
