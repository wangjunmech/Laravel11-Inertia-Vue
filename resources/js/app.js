import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { createPinia } from 'pinia';

import Main from "./Layouts/Main.vue";
import { setThemeOnLoad } from "./theme";

// import 'element-plus/dist/index.css'//干掉element-plus防止样式冲突，改用 naive-ui
import * as ElementPlusIconsVue from '@element-plus/icons-vue'//只使用ElementPlusIconsVue图标库

import naive from 'naive-ui' // 引入全部naive组件
import { debounce, cloneDeep } from 'lodash'//页面内可直接使用 this.$_.debounce() / this.$_.cloneDeep()，不用每次导入 lodash


//跨站提示函数引入 
import { CrossSiteWarning } from "./Rules/cross_site_warning.js"

//  1. 只创建一次 Pinia 实例（解决重复实例问题）
const pinia = createPinia()

createInertiaApp({
    title: (title) => `Laravel study ${title}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        page.default.layout = page.default.layout || Main;
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })

        app.use(plugin)
        app.use(ZiggyVue)
        app.use(pinia) // 复用同一个 pinia 实例
        // app.use(CrossSiteWarning)
        app.use(naive) // 全局注册naive ui



        // ✅ 全局批量注册 Element Plus 所有图标
        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
            app.component(key, component)
        }

        // 全局注册 Inertia 内置组件
        app.component("Head", Head)
        app.component("Link", Link)
        //跨站提示函数使用 
        CrossSiteWarning()
        return app.mount(el);
    },
    progress: {
        delay: 10,
        color: "#f00",
        includeCSS: true,
        showSpinner: true,
    },
});

setThemeOnLoad();