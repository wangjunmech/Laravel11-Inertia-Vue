import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { createPinia } from 'pinia'; // 引入 Pinia

import Main from "./Layouts/Main.vue";
import { setThemeOnLoad } from "./theme";

createInertiaApp({
    title: (title) => `Lavavel study ${title}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        page.default.layout = page.default.layout || Main;
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia()) // 注册并使用Pinia
            .component("Head", Head)
            .component("Link", Link)
            .mount(el);
    },
    progress: {
        delay: 10,
        color: "#f00",
        includeCSS: true,
        showSpinner: true,
    },
});

setThemeOnLoad();
