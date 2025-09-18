import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useUserPermissions } from '../organizations/useUserPermissions'
import { useOrganizationContext } from '../organizations/useOrganizationContext'

/**
 * Composable для управления навигационным меню
 * Navigation layer - управление структурой меню
 */
export function useNavigation() {
  const page = usePage()
  const { 
    canViewProjects, 
    canViewTasks, 
    canViewStatuses,
    currentUser 
  } = useUserPermissions()
  
  const { currentOrganization } = useOrganizationContext()
  
  // Базовая конфигурация меню с организационным контекстом
  const menuItems = computed(() => [
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
      badge: null,
      requiresOrganization: true
    },
    {
      key: 'projects',
      label: 'Проекты',
      href: '/projects',
      icon: 'ProjectOutlined',
      permissions: ['projects.view'],
      badge: null,
      requiresOrganization: true
    },
    {
      key: 'statuses',
      label: 'Статусы',
      href: '/statuses',
      icon: 'TagOutlined',
      permissions: ['statuses.view'],
      badge: null,
      requiresOrganization: true
    }
  ])

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

  // Проверка прав доступа с организационным контекстом
  const hasPermission = (permission) => {
    if (!permission || permission.length === 0) return true
    
    const user = page.props.auth?.user
    if (!user) return false
    
    // Используем организационные права
    switch (permission) {
      case 'tasks.view':
        return canViewTasks.value
      case 'projects.view':
        return canViewProjects.value
      case 'statuses.view':
        return canViewStatuses.value
      default:
        return true
    }
  }

  // Фильтрация видимых пунктов меню
  const getVisibleItems = computed(() => {
    return menuItems.value.filter(item => {
      // Проверяем права доступа
      if (item.permissions && item.permissions.length > 0) {
        const hasRequiredPermissions = item.permissions.some(permission => hasPermission(permission))
        if (!hasRequiredPermissions) return false
      }
      
      // Проверяем наличие организационного контекста (если требуется)
      if (item.requiresOrganization && !currentUser.value) {
        return false
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
    return menuItems.value.find(item => item.key === currentRoute)
  })

  // Получить хлебные крошки с организационным контекстом
  const getBreadcrumbs = computed(() => {
    const currentItem = getCurrentMenuItem.value
    const breadcrumbs = []

    // Добавляем главную страницу
    breadcrumbs.push({
      title: 'Главная',
      href: '/dashboard'
    })

    // Добавляем организацию если есть и страница требует организационный контекст
    if (currentOrganization.value && currentItem?.requiresOrganization) {
      breadcrumbs.push({
        title: currentOrganization.value.name,
        href: '#',
        isOrganization: true
      })
    }

    // Добавляем текущую страницу
    if (currentItem && currentItem.key !== 'dashboard') {
      breadcrumbs.push({
        title: currentItem.label,
        href: currentItem.href
      })
    }

    return breadcrumbs
  })

  // Утилиты для работы с badges
  const updateMenuBadge = (menuKey, badge) => {
    const item = menuItems.value.find(item => item.key === menuKey)
    if (item) {
      item.badge = badge
    }
  }

  const clearAllBadges = () => {
    menuItems.value.forEach(item => {
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