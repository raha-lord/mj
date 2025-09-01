import { NAVIGATION_CONFIG } from '../../config/navigation.js'

/**
 * Composable для расширения навигации
 * Позволяет динамически добавлять пункты меню
 */
export function useNavigationExtensions() {
  
  // Добавить новый пункт в основное меню
  const addMenuItem = (menuItems, newItem) => {
    const defaultItem = {
      key: newItem.key,
      label: newItem.label,
      href: newItem.href,
      icon: newItem.icon || null,
      permissions: newItem.permissions || [],
      badge: null,
      section: newItem.section || NAVIGATION_CONFIG.SECTIONS.MAIN,
      order: newItem.order || 999
    }
    
    menuItems.push(defaultItem)
    
    // Сортировка по order
    menuItems.sort((a, b) => (a.order || 999) - (b.order || 999))
    
    return menuItems
  }

  // Удалить пункт меню
  const removeMenuItem = (menuItems, key) => {
    const index = menuItems.findIndex(item => item.key === key)
    if (index > -1) {
      menuItems.splice(index, 1)
    }
    return menuItems
  }

  // Обновить пункт меню
  const updateMenuItem = (menuItems, key, updates) => {
    const item = menuItems.find(item => item.key === key)
    if (item) {
      Object.assign(item, updates)
    }
    return menuItems
  }

  // Добавить badge к пункту меню
  const addMenuBadge = (menuItems, key, badge) => {
    const item = menuItems.find(item => item.key === key)
    if (item) {
      item.badge = badge
    }
    return menuItems
  }

  // Пример: добавление админского раздела
  const addAdminSection = (menuItems) => {
    const adminItems = [
      {
        key: 'admin-users',
        label: 'Пользователи',
        href: '/admin/users',
        icon: 'TeamOutlined',
        permissions: [NAVIGATION_CONFIG.PERMISSIONS.ADMIN.VIEW_USERS],
        section: NAVIGATION_CONFIG.SECTIONS.ADMIN,
        order: 100
      },
      {
        key: 'admin-settings',
        label: 'Настройки системы',
        href: '/admin/settings',
        icon: 'SettingOutlined',
        permissions: [NAVIGATION_CONFIG.PERMISSIONS.ADMIN.SYSTEM_SETTINGS],
        section: NAVIGATION_CONFIG.SECTIONS.ADMIN,
        order: 101
      }
    ]

    adminItems.forEach(item => addMenuItem(menuItems, item))
    return menuItems
  }

  // Пример: добавление уведомлений
  const addNotificationsMenuItem = (menuItems) => {
    const notificationsItem = {
      key: 'notifications',
      label: 'Уведомления',
      href: '/notifications',
      icon: 'BellOutlined',
      permissions: [],
      order: 50,
      badge: {
        count: 3,
        color: 'red'
      }
    }

    return addMenuItem(menuItems, notificationsItem)
  }

  return {
    addMenuItem,
    removeMenuItem,
    updateMenuItem,
    addMenuBadge,
    addAdminSection,
    addNotificationsMenuItem
  }
}

// Пример использования в плагине
export const installNavigationPlugin = (menuItems) => {
  const { addNotificationsMenuItem } = useNavigationExtensions()
  
  // Можно условно добавлять пункты меню
  // if (hasNotificationsFeature) {
  //   addNotificationsMenuItem(menuItems)
  // }
  
  return menuItems
}