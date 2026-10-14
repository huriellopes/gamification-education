import { installPlugins, resolve, title } from '@/inertia';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { createSSRApp, h } from 'vue';
import { route } from '../../vendor/tightenco/ziggy';

// SSR só roda para visitantes (Inertia::disableSsr no AppServiceProvider),
// que recebem a prop `ziggy` com as rotas públicas.
createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title,
        resolve,
        setup({ App, props, plugin }) {
            // Páginas de erro são renderizadas fora do middleware do Inertia
            // (sem a prop `ziggy`): caem num config vazio em vez de quebrar o SSR.
            const ziggy = page.props.ziggy
                ? {
                      ...page.props.ziggy,
                      location: new URL(page.props.ziggy.location),
                  }
                : { url: '', port: null, defaults: {}, routes: {} };

            // Componentes chamam route() direto no <script setup> (no navegador
            // vem do window.route do @routes); no Node precisa existir no escopo global.
            globalThis.route = (name, params, absolute, config = ziggy) =>
                route(name, params, absolute, config);

            return installPlugins(
                createSSRApp({ render: () => h(App, props) }),
                plugin,
                ziggy,
            );
        },
    }),
);
