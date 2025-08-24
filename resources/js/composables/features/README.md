# Features Layer (Слой бизнес-логики)

## Назначение
Этот слой содержит **бизнес-логику** и объединяет API и UI слои для реализации конкретных функций приложения.

## Правила
- ✅ **Координация между API и UI** слоями
- ✅ **Бизнес-логика** приложения
- ✅ **Специфичные для фичи состояния** (задачи, проекты)
- ✅ **Обработка сложных операций** (создание + обновление списка)
- ❌ **НЕ содержит прямые HTTP вызовы** (использует API слой)
- ❌ **НЕ содержит низкоуровневую UI логику** (использует UI слой)

## Пример структуры composable

```javascript
// useTasksList.js
export function useTasksList() {
  const tasks = ref([])
  const loading = ref(false)
  const filters = reactive({})

  const { getTasks } = useTasks() // API слой

  const loadTasks = async () => {
    loading.value = true
    try {
      const response = await getTasks(filters)
      tasks.value = response.data
    } finally {
      loading.value = false
    }
  }

  const applyFilters = (newFilters) => {
    Object.assign(filters, newFilters)
    loadTasks()
  }

  const clearFilters = () => {
    Object.keys(filters).forEach(key => delete filters[key])
    loadTasks()
  }

  return {
    tasks: readonly(tasks),
    loading: readonly(loading),
    filters,
    loadTasks,
    applyFilters,
    clearFilters
  }
}

// useTaskModal.js
export function useTaskModal() {
  const task = ref(null)
  const loading = ref(false)
  const mode = ref('view') // 'view', 'edit', 'create'
  
  const modal = useModal() // UI слой
  const { getTask, createTask, updateTask } = useTasks() // API слой

  const openViewModal = async (taskId) => {
    mode.value = 'view'
    loading.value = true
    modal.open()
    
    try {
      const response = await getTask(taskId)
      task.value = response.data
    } finally {
      loading.value = false
    }
  }

  const saveTask = async (taskData) => {
    loading.value = true
    try {
      if (mode.value === 'create') {
        await createTask(taskData)
      } else {
        await updateTask(task.value.id, taskData)
      }
      modal.close()
    } finally {
      loading.value = false
    }
  }

  return {
    ...modal,
    task: readonly(task),
    loading: readonly(loading),
    mode: readonly(mode),
    openViewModal,
    saveTask
  }
}
```

## Взаимодействие с другими слоями
- **Используется**: Vue компонентами напрямую
- **Использует**: API слой для данных, UI слой для интерфейса