import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const basePath = (() => {
    const meta = document.querySelector('meta[name="app-base"]');

    if (! meta) {
        return '';
    }

    try {
        return new URL(meta.content, window.location.origin).pathname.replace(/\/$/, '');
    } catch {
        return '';
    }
})();

if (basePath) {
    router.on('before', (event) => {
        const url = event.detail.visit.url;

        if (! (url instanceof URL) || url.pathname === basePath || url.pathname.startsWith(`${basePath}/`)) {
            return;
        }

        url.pathname = `${basePath}${url.pathname.startsWith('/') ? url.pathname : `/${url.pathname}`}`;
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} — WedGroups` : 'WedGroups'),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
