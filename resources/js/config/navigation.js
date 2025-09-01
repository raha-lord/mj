/**
 * Конфигурация навигации приложения
 * Можно расширять для добавления новых разделов
 */

export const NAVIGATION_CONFIG = {
  // Основные разделы приложения
  SECTIONS: {
    MAIN: 'main',
    ADMIN: 'admin',
    SETTINGS: 'settings'
  },

  // Типы пунктов меню
  ITEM_TYPES: {
    LINK: 'link',
    DROPDOWN: 'dropdown',
    DIVIDER: 'divider'
  },

  // Права доступа (можно расширить)
  PERMISSIONS: {
    TASKS: {
      VIEW: 'tasks.view',
      CREATE: 'tasks.create',
      EDIT: 'tasks.edit',
      DELETE: 'tasks.delete'
    },
    PROJECTS: {
      VIEW: 'projects.view',
      CREATE: 'projects.create',
      EDIT: 'projects.edit',
      DELETE: 'projects.delete'
    },
    STATUSES: {
      VIEW: 'statuses.view',
      CREATE: 'statuses.create',
      EDIT: 'statuses.edit',
      DELETE: 'statuses.delete'
    },
    ADMIN: {
      VIEW_USERS: 'admin.users.view',
      SYSTEM_SETTINGS: 'admin.system.settings'
    }
  },

  // Настройки отображения
  DISPLAY: {
    SHOW_ICONS: true,
    SHOW_BADGES: true,
    SHOW_BREADCRUMBS: true,
    MAX_MENU_ITEMS: 10
  }
}

// Будущие расширения для ролевой модели
export const ROLE_PERMISSIONS = {
  'admin': Object.values(NAVIGATION_CONFIG.PERMISSIONS).flat(),
  'manager': [
    ...Object.values(NAVIGATION_CONFIG.PERMISSIONS.TASKS),
    ...Object.values(NAVIGATION_CONFIG.PERMISSIONS.PROJECTS),
    NAVIGATION_CONFIG.PERMISSIONS.STATUSES.VIEW
  ],
  'user': [
    NAVIGATION_CONFIG.PERMISSIONS.TASKS.VIEW,
    NAVIGATION_CONFIG.PERMISSIONS.PROJECTS.VIEW
  ]
}

export default NAVIGATION_CONFIG