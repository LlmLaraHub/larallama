import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueApexCharts from "vue3-apexcharts";
import Toast, { TYPE }  from "vue-toastification";
import { autoAnimatePlugin } from '@formkit/auto-animate/vue'


import "vue-toastification/dist/index.css";
const appName = import.meta.env.VITE_APP_NAME || 'Template App';


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexCharts)
            .use(autoAnimatePlugin)
            .use(Toast, {
                transition: "Vue-Toastification__bounce",
                pauseOnFocusLoss: false,
                maxToasts: 2,
                timeout: 2000,
                hideProgressBar: true,
                newestOnTop: true,
              })
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
