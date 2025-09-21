# API Layer (Слой API)

## Назначение
Этот слой содержит **чистые API вызовы** и отвечает только за взаимодействие с бэкендом.

## Правила
- ✅ **Только HTTP запросы** к API
- ✅ **Обработка ответов** сервера
- ✅ **Трансформация данных** из/в API формат
- ❌ **НЕ содержит UI логику** (loading, модалки, уведомления)
- ❌ **НЕ содержит бизнес-логику** (валидацию, вычисления)

## Пример структуры composable

```javascript
// useTasks.js
export function useTasks() {
  const getTasks = async (params = {}) => {
    return await apiGet('/tasks', { params })
  }

  const createTask = async (taskData) => {
    return await apiPost('/tasks', taskData)
  }

  const updateTask = async (id, taskData) => {
    return await apiPut(`/tasks/${id}`, taskData)
  }

  const deleteTask = async (id) => {
    return await apiDelete(`/tasks/${id}`)
  }

  return {
    getTasks,
    createTask,
    updateTask,
    deleteTask
  }
}
```

## Взаимодействие с другими слоями
- **Используется**: Features слоем для выполнения операций
- **Использует**: Utils слой (api.js) для HTTP запросов