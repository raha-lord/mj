/**
 * Общие вспомогательные функции
 * 
 * Этот модуль содержит переиспользуемые утилиты, которые
 * используются в различных частях приложения.
 */

/**
 * Форматировать дату в локализованном формате
 */
export const formatDate = (date, options = {}) => {
    if (!date) return ''
    
    const defaultOptions = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }
    
    try {
        return new Date(date).toLocaleDateString('ru-RU', { ...defaultOptions, ...options })
    } catch (error) {
        console.warn('Ошибка форматирования даты:', error)
        return String(date)
    }
}

/**
 * Форматировать дату и время
 */
export const formatDateTime = (date) => {
    return formatDate(date, {
        hour: '2-digit',
        minute: '2-digit'
    })
}

/**
 * Создать задержку (debounce) для функции
 */
export const debounce = (func, delay) => {
    let timeoutId
    
    return function(...args) {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => func.apply(this, args), delay)
    }
}

/**
 * Глубокое клонирование объекта
 */
export const deepClone = (obj) => {
    if (obj === null || typeof obj !== 'object') {
        return obj
    }
    
    if (obj instanceof Date) {
        return new Date(obj.getTime())
    }
    
    if (obj instanceof Array) {
        return obj.map(item => deepClone(item))
    }
    
    const cloned = {}
    for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
            cloned[key] = deepClone(obj[key])
        }
    }
    
    return cloned
}

/**
 * Проверить, является ли значение пустым
 */
export const isEmpty = (value) => {
    if (value === null || value === undefined) return true
    if (typeof value === 'string') return value.trim() === ''
    if (Array.isArray(value)) return value.length === 0
    if (typeof value === 'object') return Object.keys(value).length === 0
    return false
}

/**
 * Капитализировать первую букву строки
 */
export const capitalize = (str) => {
    if (!str || typeof str !== 'string') return ''
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}

/**
 * Генерировать случайный ID
 */
export const generateId = () => {
    return Date.now().toString(36) + Math.random().toString(36).substr(2)
}

/**
 * Извлечь числовое значение из строки
 */
export const extractNumber = (str) => {
    if (typeof str === 'number') return str
    if (typeof str !== 'string') return 0
    
    const match = str.match(/\d+/)
    return match ? parseInt(match[0], 10) : 0
}

/**
 * Преобразовать размер задачи в часы (для сортировки)
 */
export const sizeToHours = (size) => {
    const sizeMap = {
        'XS': 1,
        'S': 3,
        'M': 8,
        'L': 20,
        'XL': 40,
        'Epic': 100
    }
    
    return sizeMap[size] || 0
}

/**
 * Получить CSS класс для статуса задачи
 */
export const getStatusClass = (status) => {
    if (!status?.slug) {
        return 'status-new';
    }
    return `status-${status.slug}`;
}

/**
 * Получить CSS класс для приоритета задачи
 */
export const getPriorityClass = (priority) => {
    const priorityKey = priority?.toLowerCase() || 'normal';
    const mapping = {
        'низкий': 'priority-low',
        'low': 'priority-low',
        'обычный': 'priority-normal',
        'средний': 'priority-normal',
        'normal': 'priority-normal',
        'высокий': 'priority-high',
        'high': 'priority-high',
        'критический': 'priority-urgent',
        'срочный': 'priority-urgent',
        'urgent': 'priority-urgent'
    };
    
    return mapping[priorityKey] || 'priority-normal';
}

/**
 * Проверить валидность email
 */
export const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}

/**
 * Усечь текст до определенной длины
 */
export const truncateText = (text, maxLength = 100) => {
    if (!text || typeof text !== 'string') return ''
    if (text.length <= maxLength) return text
    return text.substring(0, maxLength).trim() + '...'
}