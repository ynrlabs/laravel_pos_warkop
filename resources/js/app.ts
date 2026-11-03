import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { ZiggyVue, route } from 'ziggy-js';
import '../css/app.css';

// Ensure route is available globally on window
(window as any).route = route;

createInertiaApp({
  title: (title) => (title ? `${title} - Warkop POS` : 'Warkop POS'),
  resolve: (name) => {
    const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });
    const ziggyConfig = (props.initialPage.props as any)?.ziggy || (window as any)?.Ziggy;

    app
      .use(plugin)
      .use(createPinia())
      .use(ZiggyVue, ziggyConfig)
      .component('Link', Link)
      .component('Head', Head);

    app.mount(el);
  },
  progress: {
    color: '#8B6B3D',
  },
});
