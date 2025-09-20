import { ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable для управления контекстом текущей организации
 * Context layer - управление текущей организацией и переключением
 */
export function useOrganizationContext() {
  const page = usePage()
  
  // Состояние
  const currentOrganization = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Получить текущего пользователя
  const currentUser = computed(() => page.props.auth?.user || null)

  // Проверить является ли пользователь супер админом
  const isSuperUser = computed(() => currentUser.value?.is_super_user || false)

  // Получить роль пользователя в текущей организации
  const currentUserRole = computed(() => {
    if (isSuperUser.value) return 'super_user'
    if (!currentOrganization.value || !currentUser.value) return null
    
    // Роль должна приходить с сервера через контекст
    // Не делаем предположений - если роль не пришла, возвращаем null
    return currentOrganization.value.user_role || null
  })

  // Проверка прав доступа
  const hasRole = (requiredRoles) => {
    if (!currentUserRole.value) return false
    if (isSuperUser.value) return true // Супер пользователь имеет все права
    
    const rolesArray = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles]
    
    // Иерархия ролей
    const roleHierarchy = {
      'super_user': ['super_user', 'org_admin', 'project_manager', 'member'],
      'org_admin': ['org_admin', 'project_manager', 'member'],
      'project_manager': ['project_manager', 'member'],
      'member': ['member']
    }

    const userPermissions = roleHierarchy[currentUserRole.value] || []
    return rolesArray.some(role => userPermissions.includes(role))
  }

  // Удобные проверки ролей
  const canManageOrganization = computed(() => hasRole(['org_admin', 'super_user']))
  const canManageProjects = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canInviteUsers = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canViewOrganization = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))

  /**
   * Получить контекст текущей организации
   */
  const fetchContext = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/organizations/context', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      currentOrganization.value = data.current_organization
      
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching organization context:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Переключиться на другую организацию
   */
  const switchOrganization = async (organizationId) => {
    if (!organizationId) {
      throw new Error('Organization ID is required')
    }

    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/organizations/switch', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify({ organization_id: organizationId })
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      currentOrganization.value = data.current_organization

      // Сохраняем в localStorage для синхронизации между вкладками
      localStorage.setItem('current_organization_id', organizationId.toString())
      
      return data.current_organization
    } catch (err) {
      error.value = err.message
      console.error('Error switching organization:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Инициализация контекста при загрузке
   */
  const initializeContext = async () => {
    try {
      await fetchContext()
    } catch (err) {
      console.warn('Failed to initialize organization context:', err)
    }
  }

  /**
   * Очистить ошибки
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Сбросить контекст
   */
  const reset = () => {
    currentOrganization.value = null
    loading.value = false
    error.value = null
    localStorage.removeItem('current_organization_id')
  }

  // Следить за изменениями организации в localStorage (синхронизация между вкладками)
  if (typeof window !== 'undefined') {
    window.addEventListener('storage', (e) => {
      if (e.key === 'current_organization_id' && e.newValue !== e.oldValue) {
        if (e.newValue) {
          // Обновляем контекст при изменении в другой вкладке
          fetchContext()
        } else {
          // Организация была очищена в другой вкладке
          currentOrganization.value = null
        }
      }
    })
  }

  return {
    // Состояние
    currentOrganization,
    loading,
    error,

    // Вычисляемые свойства
    currentUser,
    currentUserRole,
    isSuperUser,
    canManageOrganization,
    canManageProjects,
    canInviteUsers,
    canViewOrganization,

    // Методы
    hasRole,
    fetchContext,
    switchOrganization,
    initializeContext,
    clearError,
    reset
  }
}