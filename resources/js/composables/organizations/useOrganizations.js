import { ref, computed } from 'vue'

/**
 * Composable для работы с организациями
 * Business layer - управление CRUD операциями и состоянием организаций
 */
export function useOrganizations() {
  // Состояние
  const organizations = ref([])
  const currentOrganization = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Вычисляемые свойства
  const hasOrganizations = computed(() => organizations.value.length > 0)
  const canCreateOrganization = computed(() => true) // Все могут создавать организации
  const isLoadingOrganizations = computed(() => loading.value)

  /**
   * Получить список организаций пользователя
   */
  const fetchOrganizations = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await fetch('/api/organizations', {
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
      organizations.value = data.organizations || []
      
      return organizations.value
    } catch (err) {
      error.value = err.message
      console.error('Error fetching organizations:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Создать новую организацию
   */
  const createOrganization = async (organizationData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/organizations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify(organizationData)
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      const newOrganization = data.organization

      // Добавляем в локальный список
      organizations.value.unshift(newOrganization)
      
      return newOrganization
    } catch (err) {
      error.value = err.message
      console.error('Error creating organization:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Обновить организацию
   */
  const updateOrganization = async (organizationId, updateData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/organizations/${organizationId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify(updateData)
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      const updatedOrganization = data.organization

      // Обновляем в локальном списке
      const index = organizations.value.findIndex(org => org.id === organizationId)
      if (index !== -1) {
        organizations.value[index] = { ...organizations.value[index], ...updatedOrganization }
      }

      return updatedOrganization
    } catch (err) {
      error.value = err.message
      console.error('Error updating organization:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Удалить организацию (только для супер пользователей)
   */
  const deleteOrganization = async (organizationId) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/organizations/${organizationId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
      }

      // Удаляем из локального списка
      organizations.value = organizations.value.filter(org => org.id !== organizationId)
      
      return true
    } catch (err) {
      error.value = err.message
      console.error('Error deleting organization:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Получить детали организации
   */
  const fetchOrganization = async (organizationId) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/organizations/${organizationId}`, {
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
      return data.organization
    } catch (err) {
      error.value = err.message
      console.error('Error fetching organization:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Поиск организаций
   */
  const searchOrganizations = async (query, limit = 10) => {
    if (!query || query.length < 2) {
      return []
    }

    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams({
        query: query,
        limit: limit.toString()
      })

      const response = await fetch(`/api/organizations/search?${params}`, {
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
      return data.organizations || []
    } catch (err) {
      error.value = err.message
      console.error('Error searching organizations:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Очистить ошибки
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Сбросить состояние
   */
  const reset = () => {
    organizations.value = []
    currentOrganization.value = null
    loading.value = false
    error.value = null
  }

  return {
    // Состояние
    organizations,
    currentOrganization,
    loading,
    error,

    // Вычисляемые свойства
    hasOrganizations,
    canCreateOrganization,
    isLoadingOrganizations,

    // Методы
    fetchOrganizations,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    fetchOrganization,
    searchOrganizations,
    clearError,
    reset
  }
}