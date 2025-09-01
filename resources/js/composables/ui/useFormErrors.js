import { computed } from 'vue'

/**
 * Composable для интеграции Inertia ошибок с Ant Design формами
 * UI слой - только для отображения ошибок
 * 
 * @param {Object} form - Inertia form объект
 * @returns {Object} - методы для работы с ошибками
 */
export function useFormErrors(form) {
  if (!form) {
    throw new Error('form объект обязателен для useFormErrors')
  }

  /**
   * Получить настройки ошибки для Ant Design form-item
   * @param {string} field - имя поля
   * @returns {Object} - объект с validateStatus и help
   */
  const getFieldError = (field) => {
    if (!field) {
      throw new Error('field обязателен для getFieldError')
    }

    return form.errors[field] ? {
      validateStatus: 'error',
      help: form.errors[field]
    } : {}
  }

  /**
   * Проверить есть ли ошибки в форме
   */
  const hasErrors = computed(() => Object.keys(form.errors).length > 0)

  /**
   * Очистить ошибку конкретного поля
   * @param {string} field - имя поля
   */
  const clearFieldError = (field) => {
    if (!field) {
      throw new Error('field обязателен для clearFieldError')
    }

    if (form.errors[field]) {
      delete form.errors[field]
    }
  }

  /**
   * Очистить все ошибки формы
   */
  const clearAllErrors = () => {
    form.errors = {}
  }

  /**
   * Получить первую ошибку для отображения
   */
  const getFirstError = computed(() => {
    const errorKeys = Object.keys(form.errors)
    return errorKeys.length > 0 ? form.errors[errorKeys[0]] : null
  })

  return {
    getFieldError,
    hasErrors,
    clearFieldError,
    clearAllErrors,
    getFirstError
  }
}