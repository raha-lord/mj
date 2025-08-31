/**
 * Система уведомлений (UI слой)
 * 
 * Отвечает ТОЛЬКО за состояние уведомлений:
 * - Показ/скрытие уведомлений
 * - Управление списком уведомлений
 * - Изолированное состояние (SRP)
 * 
 * НЕ содержит: API вызовы, бизнес-логику
 */

import { ref, readonly } from 'vue'
import { generateId } from '@/utils/helpers.js'

/**
 * Система уведомлений
 * 
 * @returns {Object} { notifications, showNotification, hideNotification }
 */
export function useNotifications() {
    const notifications = ref([])

    /**
     * Показать уведомление
     * 
     * @param {string} message - Текст уведомления
     * @param {string} type - Тип уведомления ('success', 'error', 'warning', 'info')
     * @param {number} duration - Длительность показа в мс (0 = не исчезает)
     */
    const showNotification = (message, type = 'info', duration = 5000) => {
        if (!message) {
            console.warn('showNotification: пустое сообщение')
            return
        }

        const id = generateId()
        const notification = { 
            id, 
            message, 
            type, 
            timestamp: Date.now()
        }

        notifications.value.push(notification)

        // Автоматически убрать уведомление через duration
        if (duration > 0) {
            setTimeout(() => {
                hideNotification(id)
            }, duration)
        }

        return id
    }

    /**
     * Скрыть уведомление по ID
     * 
     * @param {string} id - ID уведомления
     */
    const hideNotification = (id) => {
        if (!id) return

        const index = notifications.value.findIndex(n => n.id === id)
        if (index > -1) {
            notifications.value.splice(index, 1)
        }
    }

    /**
     * Очистить все уведомления
     */
    const clearNotifications = () => {
        notifications.value.splice(0)
    }

    return {
        notifications: readonly(notifications),
        showNotification,
        hideNotification,
        clearNotifications
    }
}