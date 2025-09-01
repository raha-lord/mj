import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable для управления навигационным меню
 * Navigation layer - управление структурой меню
 */
export function useNavigation() {
  const page = usePage()
  
  // Базовая конфигурация меню
  const menuItems = [
    {
      key: 'dashboard',
      label: 'Dashboard',
      href: '/dashboard',
      icon: 'DashboardOutlined',
      permissions: [],
      badge: null
    },
    {
      key: 'tasks',
      label: 'Задачи',
      href: '/tasks',
      icon: 'FileTextOutlined',
      permissions: ['tasks.view'],
      badge: null
    },
    {
      key: 'projects',
      label: 'Проекты',
      href: '/projects',
      icon: 'ProjectOutlined',
      permissions: ['projects.view'],
      badge: null
    },
    {
      key: 'statuses',
      label: 'Статусы',
      href: '/statuses',
      icon: 'TagOutlined',
      permissions: ['statuses.view'],
      badge: null
    }
  ]

  // Профильное меню
  const profileMenuItems = [
    {
      key: 'profile',
      label: 'Профиль',
      href: '/profile',
      icon: 'UserOutlined',
      permissions: []
    },
    {
      key: 'settings',
      label: 'Настройки',
      href: '/profile/edit',
      icon: 'SettingOutlined',
      permissions: []
    },
    {
      type: 'divider'
    },
    {
      key: 'logout',
      label: 'Выйти',
      href: '/logout',
      icon: 'LogoutOutlined',
      method: 'post',
      permissions: []
    }
  ]

  // Получить текущий маршрут
  const getCurrentRoute = computed(() => {
    const url = page.url
    
    // Определяем активный пункт меню на основе URL
    if (url.includes('/tasks')) return 'tasks'
    if (url.includes('/projects')) return 'projects'  
    if (url.includes('/statuses')) return 'statuses'
    if (url.includes('/profile')) return 'profile'
    if (url === '/dashboard' || url === '/') return 'dashboard'
    
    return null
  })

  // Проверка прав доступа (пока заглушка)
  const hasPermission = (permission) => {
    if (!permission || permission.length === 0) return true
    
    // TODO: Реализовать проверку прав через user permissions
    const user = page.props.auth?.user
    if (!user) return false
    
    // Пока возвращаем true для всех авторизованных пользователей
    return true
  }

  // Фильтрация видимых пунктов меню
  const getVisibleItems = computed(() => {
    return menuItems.filter(item => {
      // Проверяем права доступа
      if (item.permissions && item.permissions.length > 0) {
        return item.permissions.some(permission => hasPermission(permission))
      }
      return true
    })
  })

  // Фильтрация видимых пунктов профильного меню
  const getVisibleProfileItems = computed(() => {
    return profileMenuItems.filter(item => {
      if (item.type === 'divider') return true
      
      if (item.permissions && item.permissions.length > 0) {
        return item.permissions.some(permission => hasPermission(permission))
      }
      return true
    })
  })

  // Получить информацию о текущем пункте меню
  const getCurrentMenuItem = computed(() => {
    const currentRoute = getCurrentRoute.value
    return menuItems.find(item => item.key === currentRoute)
  })

  // Получить хлебные крошки
  const getBreadcrumbs = computed(() => {
    const currentItem = getCurrentMenuItem.value
    if (!currentItem) return []

    const breadcrumbs = [
      {
        title: 'Главная',
        href: '/dashboard'
      }
    ]

    if (currentItem.key !== 'dashboard') {
      breadcrumbs.push({
        title: currentItem.label,
        href: currentItem.href
      })
    }

    return breadcrumbs
  })

  // Утилиты для работы с badges
  const updateMenuBadge = (menuKey, badge) => {
    const item = menuItems.find(item => item.key === menuKey)
    if (item) {
      item.badge = badge
    }
  }

  const clearAllBadges = () => {
    menuItems.forEach(item => {
      item.badge = null
    })
  }

  return {
    // Данные
    menuItems,
    profileMenuItems,
    
    // Computed
    getCurrentRoute,
    getVisibleItems,
    getVisibleProfileItems,
    getCurrentMenuItem,
    getBreadcrumbs,
    
    // Методы
    hasPermission,
    updateMenuBadge,
    clearAllBadges
  }
}