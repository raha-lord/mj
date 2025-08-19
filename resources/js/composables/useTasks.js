import { ref } from 'vue'

export function useTasks() {
  const loading = ref(false)
  const error = ref(null)

  // Получение CSRF токена
  const getCsrfToken = () => {
    const metaTag = document.querySelector('meta[name="csrf-token"]')
    return metaTag ? metaTag.getAttribute('content') : null
  }

  // Базовые настройки для fetch
  const getDefaultHeaders = () => ({
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': getCsrfToken(),
    'X-Requested-With': 'XMLHttpRequest',
  })

  // Обработка ответа API
  const handleApiResponse = async (response) => {
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw errorData
    }
    return await response.json()
  }

  // Создание задачи
  const createTask = async (taskData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/tasks/', {
        method: 'POST',
        headers: getDefaultHeaders(),
        credentials: 'same-origin',
        body: JSON.stringify(taskData)
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        return result
      } else {
        throw new Error(result.message || 'Ошибка при создании задачи')
      }
    } catch (err) {
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  // Обновление задачи
  const updateTask = async (taskId, taskData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: getDefaultHeaders(),
        credentials: 'same-origin',
        body: JSON.stringify(taskData)
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        return result
      } else {
        throw new Error(result.message || 'Ошибка при обновлении задачи')
      }
    } catch (err) {
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  // Получение задачи
  const getTask = async (taskId) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'GET',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        return result.data
      } else {
        throw new Error(result.message || 'Ошибка при загрузке задачи')
      }
    } catch (err) {
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  // Получение списка задач с фильтрами
  const getTasks = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== null && value !== '') {
          params.append(key, value)
        }
      })

      const url = `/api/tasks/table-html${params.toString() ? '?' + params.toString() : ''}`
      
      const response = await fetch(url, {
        method: 'GET',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        return result
      } else {
        throw new Error(result.message || 'Ошибка при загрузке задач')
      }
    } catch (err) {
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  // Удаление задачи
  const deleteTask = async (taskId) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'DELETE',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        return result
      } else {
        throw new Error(result.message || 'Ошибка при удалении задачи')
      }
    } catch (err) {
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    createTask,
    updateTask,
    getTask,
    getTasks,
    deleteTask
  }
}