/**
 * Базовая логика модальных окон (UI слой)
 * 
 * Отвечает ТОЛЬКО за состояние модальных окон:
 * - Открытие/закрытие модалки
 * - Изолированное состояние (SRP)
 * 
 * НЕ содержит: API вызовы, бизнес-логику, данные
 */

import { ref, readonly } from 'vue'

/**
 * Базовая логика для модального окна
 * 
 * @returns {Object} { isOpen, open, close }
 */
export function useModal() {
    const isOpen = ref(false)

    /**
     * Открыть модальное окно
     */
    const open = () => {
        isOpen.value = true
    }

    /**
     * Закрыть модальное окно
     */
    const close = () => {
        isOpen.value = false
    }

    return {
        isOpen: readonly(isOpen),
        open,
        close
    }
}