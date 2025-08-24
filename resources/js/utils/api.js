/**
 * Базовые утилиты для работы с API
 * 
 * Этот модуль содержит низкоуровневые функции для HTTP запросов
 * и обработки ответов API. Используется другими модулями для
 * выполнения сетевых операций.
 */

/**
 * Базовая конфигурация для всех API запросов
 */
const apiConfig = {
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
}

/**
 * Получить CSRF токен из мета-тега
 */
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!token) {
        console.warn('CSRF token не найден в документе')
    }
    return token
}

/**
 * Создать заголовки для запроса
 */
const createHeaders = (additionalHeaders = {}) => {
    const csrfToken = getCsrfToken()
    const headers = { ...apiConfig.headers, ...additionalHeaders }
    
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    return headers
}

/**
 * Обработать ответ от API
 */
const handleResponse = async (response) => {
    const contentType = response.headers.get('content-type')
    
    if (!response.ok) {
        let errorMessage = `HTTP ${response.status}: ${response.statusText}`
        
        try {
            if (contentType?.includes('application/json')) {
                const errorData = await response.json()
                errorMessage = errorData.message || errorMessage
            } else {
                errorMessage = await response.text() || errorMessage
            }
        } catch (e) {
            // Используем стандартное сообщение об ошибке
        }
        
        throw new Error(errorMessage)
    }
    
    if (contentType?.includes('application/json')) {
        return await response.json()
    }
    
    return await response.text()
}

/**
 * Выполнить GET запрос
 */
export const apiGet = async (url, options = {}) => {
    const response = await fetch(`${apiConfig.baseURL}${url}`, {
        method: 'GET',
        headers: createHeaders(options.headers),
        ...options
    })
    
    return handleResponse(response)
}

/**
 * Выполнить POST запрос
 */
export const apiPost = async (url, data = null, options = {}) => {
    const body = data ? JSON.stringify(data) : null
    
    const response = await fetch(`${apiConfig.baseURL}${url}`, {
        method: 'POST',
        headers: createHeaders(options.headers),
        body,
        ...options
    })
    
    return handleResponse(response)
}

/**
 * Выполнить PUT запрос
 */
export const apiPut = async (url, data = null, options = {}) => {
    const body = data ? JSON.stringify(data) : null
    
    const response = await fetch(`${apiConfig.baseURL}${url}`, {
        method: 'PUT',
        headers: createHeaders(options.headers),
        body,
        ...options
    })
    
    return handleResponse(response)
}

/**
 * Выполнить DELETE запрос
 */
export const apiDelete = async (url, options = {}) => {
    const response = await fetch(`${apiConfig.baseURL}${url}`, {
        method: 'DELETE',
        headers: createHeaders(options.headers),
        ...options
    })
    
    return handleResponse(response)
}

/**
 * Построить URL с query параметрами
 */
export const buildUrlWithParams = (baseUrl, params = {}) => {
    const url = new URL(baseUrl, window.location.origin)
    
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.append(key, value)
        }
    })
    
    return url.pathname + url.search
}