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
  const menuItems = computed(() => {
    const items = []
    
    // Dashboard всегда первый
    items.push({
      key: 'dashboard',
      label: 'Dashboard',
      href: '/dashboard',
      icon: 'DashboardOutlined',
      permissions: [],
      badge: null
    })
    
    // Меню для SuperUser
    if (currentUser.value?.is_super_user) {
      items.push(
        {
          key: 'organizations',
          label: 'Организации',
          href: '/organizations',
          icon: 'TeamOutlined',
          permissions: [],
          badge: null
        },
        {
          key: 'users',
          label: 'Пользователи',
          href: '/users',
          icon: 'UserOutlined',
          permissions: [],
          badge: null
        },
        {
          key: 'projects',
          label: 'Проекты',
          href: '/projects',
          icon: 'ProjectOutlined',
          permissions: [],
          badge: null
        },
        {
          key: 'tasks',
          label: 'Задачи',
          href: '/tasks',
          icon: 'FileTextOutlined',
          permissions: [],
          badge: null
        }
      )
    }
    // Меню для обычных пользователей (Admin и Member)
    else {
      items.push(
        {
          key: 'projects',
          label: 'Проекты',
          href: '/projects',
          icon: 'ProjectOutlined',
          permissions: [],
          badge: null
        },
        {
          key: 'tasks',
          label: 'Задачи',
          href: '/tasks',
          icon: 'FileTextOutlined',
          permissions: [],
          badge: null
        }
      )
    }
    
    return items
  })

  // Профильное меню
  const profileMenuItems = computed(() => {
    const items = [
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
      }
    ]


    items.push(
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
    )

    return items
  })

  // Получить текущий маршрут
  const getCurrentRoute = computed(() => {
    const url = page.url
    
    // Определяем активный пункт меню на основе URL
    if (url.includes('/organizations')) return 'organizations'
    if (url.includes('/users')) return 'users'
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
    
    // Супер пользователь имеет все права
    if (user.is_super_user) return true
    
    // Для обычных пользователей проверяем через useUserPermissions
    switch (permission) {
      case 'tasks.view':
        return canViewTasks?.value || false
      case 'projects.view':
        return canViewProjects?.value || false
      case 'statuses.view':
        return canViewStatuses?.value || false
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
      
      return true
    })
  })

  // Фильтрация видимых пунктов профильного меню
  const getVisibleProfileItems = computed(() => {
    return profileMenuItems.value.filter(item => {
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