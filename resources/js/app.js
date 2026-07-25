import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/inertia-vue3';
import { createPinia } from 'pinia';
import { ZiggyVue } from 'ziggy-js';
import { InertiaProgress } from '@inertiajs/progress';
import '../css/app.css';

// Progress bar
InertiaProgress.init({
    color: '#8B6B3D',
    includeCSS: true,
    showSpinner: false,
});

createInertiaApp({
    title: (title) => `${title} - Warkop POS`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin)
           .use(createPinia())
           .use(ZiggyVue)
           .component('Link', Link)
           .component('Head', Head);
        
        app.mount(el);
    },
    progress: {
        color: '#8B6B3D',
    },
});