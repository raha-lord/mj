/**
 * Логика списка задач (Features слой)
 * 
 * Координирует API и UI слои для управления списком задач:
 * - Загрузка и управление списком задач
 * - Управление фильтрами
 * - Изолированное состояние (НЕ пересекается с модалками)
 * 
 * Использует: API слой (useTasks)
 * НЕ содержит: UI логику модалок, прямые HTTP вызовы
 */

import { ref, reactive, readonly } from 'vue'
import { useTasks } from '@/composables/api/useTasks.js'

/**
 * Управление списком задач
 */
export function useTasksList() {
    // Изолированное состояние только для списка
    const tasks = ref([])
    const loading = ref(false)
    const pagination = ref(null)
    const filters = reactive({
        search: '',
        status: '',
        project_id: '',
        assignee: '',
        priority: '',
        size: '',
        date_from: '',
        date_to: ''
    })

    // API слой
    const tasksApi = useTasks()

    /**
     * Загрузить список задач как JSON объекты
     */
    const loadTasks = async () => {
        loading.value = true
        
        try {
            const result = await tasksApi.getTasksJson(filters)
            // Обновляем JSON объекты (новая реактивная система)
            if (result.data) {
                tasks.value = result.data
                pagination.value = result.pagination
            }
            return result
        } finally {
            loading.value = false
        }
    }

    /**
     * Загрузить список задач как HTML (для совместимости)
     */
    const loadTasksHtml = async () => {
        loading.value = true
        
        try {
            const result = await tasksApi.getTasks(filters)
            // Обновляем HTML (старая система)
            if (result.html) {
                tasks.value = result.html
            }
            return result
        } finally {
            loading.value = false
        }
    }

    /**
     * Применить фильтры и обновить список
     */
    const applyFilters = async (newFilters = {}) => {
        Object.assign(filters, newFilters)
        return await loadTasks()
    }

    /**
     * Очистить все фильтры
     */
    const clearFilters = async () => {
        Object.keys(filters).forEach(key => {
            filters[key] = ''
        })
        return await loadTasks()
    }

    /**
     * Обновить конкретный фильтр
     */
    const updateFilter = async (filterName, value) => {
        if (filters.hasOwnProperty(filterName)) {
            filters[filterName] = value
            return await loadTasks()
        }
    }

    /**
     * Явное обновление списка после изменений
     */
    const refreshTasks = async () => {
        return await loadTasks()
    }

    return {
        // Состояние только для списка
        tasks: readonly(tasks),
        loading: readonly(loading),
        pagination: readonly(pagination),
        filters, // reactive для двунаправленного связывания

        // Методы управления списком
        loadTasks,
        loadTasksHtml,
        applyFilters,
        clearFilters,
        updateFilter,
        refreshTasks
    }
}