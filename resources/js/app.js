import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css'

// Импорт системы тем
import '../js-legacy/utils/themeManager.js'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(Antd)

    // Глобальная обработка ошибок Vue
    app.config.errorHandler = (err, instance, info) => {
      console.error('Vue error:', err, instance, info)
    }

    app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})