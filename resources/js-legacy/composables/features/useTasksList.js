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
        date_to: '',
        page: 1,
        per_page: 25
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
        filters.page = 1 // Сброс на первую страницу при изменении фильтров
        return await loadTasks()
    }

    /**
     * Очистить все фильтры
     */
    const clearFilters = async () => {
        Object.keys(filters).forEach(key => {
            if (key === 'page') {
                filters[key] = 1
            } else if (key === 'per_page') {
                filters[key] = 25
            } else {
                filters[key] = ''
            }
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

    /**
     * Изменить страницу
     */
    const changePage = async (page) => {
        filters.page = page
        return await loadTasks()
    }

    /**
     * Изменить количество записей на странице
     */
    const changePerPage = async (currentPage, perPage) => {
        console.log('changePerPage called with:', { currentPage, perPage })
        filters.per_page = Number(perPage)
        filters.page = 1 // Сброс на первую страницу
        console.log('Updated filters:', { page: filters.page, per_page: filters.per_page })
        return await loadTasks()
    }

    /**
     * Сброс пагинации на первую страницу
     */
    const resetPagination = () => {
        filters.page = 1
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
        refreshTasks,
        
        // Методы пагинации
        changePage,
        changePerPage,
        resetPagination
    }
}