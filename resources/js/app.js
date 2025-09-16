import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import Antd, { ConfigProvider } from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css'

// Импорт системы тем
import '../js-legacy/utils/themeManager.js'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Расширенная настройка темы
const antdTheme = {
  token: {
    // Основные цвета
    colorPrimary: '#52c41a',
    colorSuccess: '#52c41a',

    // Hover состояния
    colorPrimaryHover: '#091104',
    colorSuccessHover: '#7e254a',
    colorBgTextHover: 'rgba(82, 196, 26, 0.06)',

    // Active состояния
    colorPrimaryActive: '#389e0d',
    colorSuccessActive: '#389e0d',
    colorBgTextActive: 'rgba(82, 196, 26, 0.15)',

    // Border цвета
    colorPrimaryBorder: '#52c41a',
    colorPrimaryBorderHover: '#73d13d',

    // Тени при фокусе
    boxShadowSecondary: '0 0 0 2px rgba(82, 196, 26, 0.2)',
  },

  components: {
    Layout: {
      headerBg: '#ffffff',
      headerColor: '#333333',
    },

    Button: {
      // Primary кнопки
      primaryColor: '#ffffff',
      primaryBg: '#52c41a',
      primaryColorHover: '#ffffff',
      primaryBgHover: '#73d13d',
      primaryColorActive: '#ffffff',
      primaryBgActive: '#389e0d',

      // Default кнопки
      defaultColor: '#333333',
      defaultBg: '#ffffff',
      defaultBorder: '#d9d9d9',
      defaultColorHover: '#52c41a',
      defaultBgHover: 'rgba(82, 196, 26, 0.06)',
      defaultBorderColorHover: '#52c41a',

      // Link кнопки
      linkColor: '#52c41a',
      linkColorHover: '#73d13d',
      linkColorActive: '#389e0d',
    },

    Menu: {
      colorBg: 'transparent',
      itemColor: '#333333',
      itemHoverBg: 'rgba(82, 196, 26, 0.08)',
      itemHoverColor: '#1a31c4',
      itemSelectedBg: 'rgba(161,172,217,0.12)',
      itemSelectedColor: '#0a0a08',
      itemActiveBg: 'rgba(82, 196, 26, 0.15)',
    },

    Dropdown: {
      colorBg: '#ffffff',
      colorText: '#333333',
    },

    Input: {
      colorBorder: '#d9d9d9',
      colorBorderHover: '#73d13d',
      activeBorderColor: '#52c41a',
      hoverBorderColor: '#73d13d',
    },

    Select: {
      colorBorder: '#d9d9d9',
      colorBorderHover: '#73d13d',
      activeBorderColor: '#52c41a',
      optionSelectedBg: 'rgba(82, 196, 26, 0.1)',
      optionSelectedColor: '#52c41a',
      optionActiveBg: 'rgba(82, 196, 26, 0.06)',
    }
  }
}

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(ConfigProvider, { theme: antdTheme }, {
        default: () => h(App, props)
      })
    })
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