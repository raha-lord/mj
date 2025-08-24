/**
 * Логика модальных окон задач (Features слой)
 * 
 * Координирует API и UI слои для управления модалками задач:
 * - Открытие/закрытие модалок в разных режимах
 * - Загрузка и сохранение данных задач
 * - Изолированное состояние (НЕ пересекается со списком)
 * 
 * Использует: API слой (useTasks), UI слой (useModal, useNotifications)
 * НЕ содержит: логику списка, прямые HTTP вызовы
 */

import { ref, readonly } from 'vue'
import { useTasks } from '@/composables/api/useTasks.js'
import { useModal } from '@/composables/ui/useModal.js'
import { useNotifications } from '@/composables/ui/useNotifications.js'

/**
 * Управление модальными окнами задач
 */
export function useTaskModal() {
    // Изолированное состояние только для модалки
    const task = ref(null)
    const loading = ref(false)
    const mode = ref('view') // 'view', 'edit', 'create'

    // UI и API слои
    const modal = useModal()
    const tasksApi = useTasks()
    const notifications = useNotifications()

    /**
     * Открыть модалку просмотра задачи
     */
    const openViewModal = async (taskId) => {
        if (!taskId) {
            console.warn('openViewModal: taskId обязателен')
            return
        }

        mode.value = 'view'
        task.value = null
        loading.value = true
        modal.open()

        try {
            const taskData = await tasksApi.getTask(taskId)
            task.value = taskData
        } catch (error) {
            notifications.showNotification(
                'Ошибка загрузки задачи: ' + error.message, 
                'error'
            )
            modal.close()
        } finally {
            loading.value = false
        }
    }

    /**
     * Открыть модалку редактирования задачи
     */
    const openEditModal = async (taskId) => {
        if (!taskId) {
            console.warn('openEditModal: taskId обязателен')
            return
        }

        mode.value = 'edit'
        task.value = null
        loading.value = true
        modal.open()

        try {
            const taskData = await tasksApi.getTask(taskId)
            task.value = taskData
        } catch (error) {
            notifications.showNotification(
                'Ошибка загрузки задачи: ' + error.message, 
                'error'
            )
            modal.close()
        } finally {
            loading.value = false
        }
    }

    /**
     * Открыть модалку создания новой задачи
     */
    const openCreateModal = () => {
        mode.value = 'create'
        task.value = null
        loading.value = false
        modal.open()
    }

    /**
     * Закрыть модалку
     */
    const closeModal = () => {
        modal.close()
        task.value = null
        mode.value = 'view'
    }

    /**
     * Сохранить задачу (создать или обновить)
     */
    const saveTask = async (taskData) => {
        if (!taskData) {
            notifications.showNotification('Данные задачи обязательны', 'error')
            return false
        }

        loading.value = true

        try {
            let result
            if (mode.value === 'create') {
                result = await tasksApi.createTask(taskData)
                notifications.showNotification('Задача успешно создана', 'success')
            } else if (mode.value === 'edit' && task.value?.id) {
                result = await tasksApi.updateTask(task.value.id, taskData)
                notifications.showNotification('Задача успешно обновлена', 'success')
            }

            if (result?.success) {
                closeModal()
                return true
            }
        } catch (error) {
            notifications.showNotification(
                'Ошибка сохранения: ' + error.message, 
                'error'
            )
        } finally {
            loading.value = false
        }

        return false
    }

    return {
        // Состояние только для модалки
        task: readonly(task),
        loading: readonly(loading),
        mode: readonly(mode),
        isOpen: modal.isOpen,

        // Методы управления модалкой
        openViewModal,
        openEditModal,
        openCreateModal,
        closeModal,
        saveTask
    }
}