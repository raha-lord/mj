# UI Layer (Слой UI)

## Назначение
Этот слой содержит **UI логику** и отвечает за поведение пользовательского интерфейса.

## Правила
- ✅ **Управление состоянием UI** (модалки, уведомления, loading)
- ✅ **Переиспользуемая UI логика** (модалки, пагинация, фильтры)
- ✅ **Обработка UI событий** (открытие/закрытие модалок)
- ❌ **НЕ содержит API вызовы** (только UI состояния)
- ❌ **НЕ содержит бизнес-логику** (валидацию, вычисления)

## Пример структуры composable

```javascript
// useModal.js
export function useModal() {
  const isOpen = ref(false)

  const open = () => {
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  return {
    isOpen: readonly(isOpen),
    open,
    close
  }
}

// useNotifications.js  
export function useNotifications() {
  const notifications = ref([])

  const showNotification = (message, type = 'info') => {
    const id = generateId()
    notifications.value.push({ id, message, type })
  }

  const hideNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  return {
    notifications: readonly(notifications),
    showNotification,
    hideNotification
  }
}
```

## Взаимодействие с другими слоями
- **Используется**: Features слоем и Vue компонентами
- **Использует**: Utils слой для вспомогательных функций