/**
 * API для работы с задачами (API слой)
 * 
 * Отвечает ТОЛЬКО за HTTP запросы к API задач:
 * - CRUD операции с задачами
 * - Разделенные loading состояния по операциям (SRP)
 * - Обработка ошибок API
 * 
 * НЕ содержит: UI логику, бизнес-логику
 */

import { ref, readonly } from 'vue'
import { apiGet, apiPost, apiPut, apiDelete, buildUrlWithParams } from '@/utils/api.js'

/**
 * API для работы с задачами
 */
export function useTasks() {
    // Разделенные loading состояния (SRP - каждая операция имеет свое состояние)
    const createLoading = ref(false)
    const updateLoading = ref(false)
    const getLoading = ref(false)
    const deleteLoading = ref(false)
    const listLoading = ref(false)

    const error = ref(null)

    /**
     * Создание задачи
     */
    const createTask = async (taskData) => {
        if (!taskData) {
            throw new Error('taskData обязателен')
        }

        createLoading.value = true
        error.value = null

        try {
            const result = await apiPost('/tasks/', taskData)
            
            if (result.success) {
                return result
            } else {
                throw new Error(result.message || 'Ошибка при создании задачи')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            createLoading.value = false
        }
    }

    /**
     * Обновление задачи
     */
    const updateTask = async (taskId, taskData) => {
        if (!taskId || !taskData) {
            throw new Error('taskId и taskData обязательны')
        }

        updateLoading.value = true
        error.value = null

        try {
            const result = await apiPut(`/tasks/${taskId}`, taskData)
            
            if (result.success) {
                return result
            } else {
                throw new Error(result.message || 'Ошибка при обновлении задачи')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            updateLoading.value = false
        }
    }

    /**
     * Получение одной задачи по ID
     */
    const getTask = async (taskId) => {
        if (!taskId) {
            throw new Error('taskId обязателен')
        }

        getLoading.value = true
        error.value = null

        try {
            const result = await apiGet(`/tasks/${taskId}`)
            
            if (result.success) {
                return result.data
            } else {
                throw new Error(result.message || 'Ошибка при загрузке задачи')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            getLoading.value = false
        }
    }

    /**
     * Получение списка задач с фильтрами (HTML для совместимости)
     */
    const getTasks = async (filters = {}) => {
        listLoading.value = true
        error.value = null

        try {
            const url = buildUrlWithParams('/tasks/table-html', filters)
            const result = await apiGet(url)
            
            if (result.success) {
                return result
            } else {
                throw new Error(result.message || 'Ошибка при загрузке задач')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            listLoading.value = false
        }
    }

    /**
     * Получение списка задач как JSON объекты
     */
    const getTasksJson = async (filters = {}) => {
        listLoading.value = true
        error.value = null

        try {
            const url = buildUrlWithParams('/tasks', filters)
            const result = await apiGet(url)
            
            if (result.success) {
                return {
                    success: true,
                    data: result.data.tasks,
                    pagination: result.data.pagination
                }
            } else {
                throw new Error(result.message || 'Ошибка при загрузке задач')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            listLoading.value = false
        }
    }

    /**
     * Удаление задачи
     */
    const deleteTask = async (taskId) => {
        if (!taskId) {
            throw new Error('taskId обязателен')
        }

        deleteLoading.value = true
        error.value = null

        try {
            const result = await apiDelete(`/tasks/${taskId}`)
            
            if (result.success) {
                return result
            } else {
                throw new Error(result.message || 'Ошибка при удалении задачи')
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            deleteLoading.value = false
        }
    }

    return {
        // Разделенные loading состояния
        createLoading: readonly(createLoading),
        updateLoading: readonly(updateLoading),
        getLoading: readonly(getLoading),
        deleteLoading: readonly(deleteLoading),
        listLoading: readonly(listLoading),
        
        error: readonly(error),
        
        // API методы
        createTask,
        updateTask,
        getTask,
        getTasks,
        getTasksJson,
        deleteTask
    }
}