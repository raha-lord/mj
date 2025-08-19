import { ref } from 'vue'

export function useTasks() {
  const loading = ref(false)
  const error = ref(null)

  // Получение CSRF токена с проверкой
  const getCsrfToken = () => {
    try {
      const metaTag = document.querySelector('meta[name="csrf-token"]')
      if (!metaTag) {
        console.warn('CSRF токен не найден в meta тегах')
        return null
      }
      return metaTag.getAttribute('content')
    } catch (err) {
      console.error('Ошибка получения CSRF токена:', err)
      return null
    }
  }

  // Базовые настройки для fetch с обработкой ошибок
  const getDefaultHeaders = () => {
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
    
    const csrfToken = getCsrfToken()
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    return headers
  }

  // Обработка ответа API с улучшенной обработкой ошибок
  const handleApiResponse = async (response) => {
    let data
    try {
      data = await response.json()
    } catch (jsonError) {
      console.error('Ошибка парсинга JSON:', jsonError)
      throw new Error(`Ошибка сервера: ${response.status} ${response.statusText}`)
    }

    if (!response.ok) {
      console.error('API ошибка:', response.status, data)
      throw data
    }
    
    return data
  }

  // Создание задачи
  const createTask = async (taskData) => {
    loading.value = true
    error.value = null

    try {
      console.log('Создание задачи:', taskData)
      
      const response = await fetch('/api/tasks/', {
        method: 'POST',
        headers: getDefaultHeaders(),
        credentials: 'same-origin',
        body: JSON.stringify(taskData)
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        console.log('Задача создана успешно:', result)
        return result
      } else {
        throw new Error(result.message || 'Ошибка при создании задачи')
      }
    } catch (err) {
      console.error('Ошибка создания задачи:', err)
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
      console.log('Обновление задачи:', taskId, taskData)
      
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: getDefaultHeaders(),
        credentials: 'same-origin',
        body: JSON.stringify(taskData)
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        console.log('Задача обновлена успешно:', result)
        return result
      } else {
        throw new Error(result.message || 'Ошибка при обновлении задачи')
      }
    } catch (err) {
      console.error('Ошибка обновления задачи:', err)
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
      console.log('Получение задачи:', taskId)
      
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'GET',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        console.log('Задача получена успешно:', result)
        return result.data
      } else {
        throw new Error(result.message || 'Ошибка при загрузке задачи')
      }
    } catch (err) {
      console.error('Ошибка получения задачи:', err)
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
      console.log('Получение списка задач с фильтрами:', filters)
      
      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
          params.append(key, value)
        }
      })

      const url = `/api/tasks/table-html${params.toString() ? '?' + params.toString() : ''}`
      console.log('URL запроса:', url)
      
      const response = await fetch(url, {
        method: 'GET',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        console.log('Список задач получен успешно')
        return result
      } else {
        throw new Error(result.message || 'Ошибка при загрузке задач')
      }
    } catch (err) {
      console.error('Ошибка получения списка задач:', err)
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
      console.log('Удаление задачи:', taskId)
      
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'DELETE',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
      })

      const result = await handleApiResponse(response)
      
      if (result.success) {
        console.log('Задача удалена успешно:', result)
        return result
      } else {
        throw new Error(result.message || 'Ошибка при удалении задачи')
      }
    } catch (err) {
      console.error('Ошибка удаления задачи:', err)
      error.value = err
      throw err
    } finally {
      loading.value = false
    }
  }

  console.log('useTasks композабл инициализирован')

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