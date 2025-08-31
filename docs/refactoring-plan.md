# 🚀 План реструктуризации архитектуры Vue компонентов

## 📋 Цель реструктуризации
Привести код к архитектурным принципам из `docs/project_princps.txt` и структуре из `docs/vue.txt`:
- **SRP** - разделить состояния по ответственности
- **Композиция** - собрать логику из простых блоков
- **Слоистая архитектура** - четкое разделение API/UI/Features
- **Явность** - убрать неожиданные побочные эффекты

---

## 📋 **ОБЯЗАТЕЛЬНО ПЕРЕД КАЖДЫМ ЭТАПОМ:**
> **Открой и используй:** `docs/development-checklist.md`
> Каждый файл/функция проверяется по чек-листу!

## 🎯 Этапы реализации

### ✅ **ЭТАП 1: Создание базовой структуры папок и utils**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Создаем папки `composables/{api,ui,features}` и `utils/`
- Создаем `utils/api.js` - базовые утилиты для API
- Создаем `utils/helpers.js` - общие хелперы
- Документируем назначение каждого слоя

#### Файлы:
- `resources/js/composables/api/`
- `resources/js/composables/ui/`
- `resources/js/composables/features/`
- `resources/js/utils/api.js`
- `resources/js/utils/helpers.js`

#### Критерии проверки:
- [ ] Папки созданы
- [ ] `utils/api.js` содержит базовые функции для HTTP запросов
- [ ] `utils/helpers.js` содержит общие утилиты
- [ ] Структура соответствует `docs/vue.txt`
- [ ] ✅ **Чек-лист**: каждый файл проверен по `development-checklist.md`

#### Как проверять:
```bash
ls -la resources/js/composables/
ls -la resources/js/utils/
# Должны быть: api/, ui/, features/, utils/
```

---

### ✅ **ЭТАП 2: Создание базового UI слоя**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Создаем `composables/ui/useModal.js` - базовая логика модалок
- Создаем `composables/ui/useNotifications.js` - система уведомлений

#### Файлы:
- `resources/js/composables/ui/useModal.js`
- `resources/js/composables/ui/useNotifications.js`

#### Критерии проверки:
- [ ] `useModal.js` экспортирует: `{ isOpen, open, close }`
- [ ] `useNotifications.js` экспортирует: `{ notifications, showNotification, hideNotification }`
- [ ] Каждый composable имеет только СВОЕ состояние (SRP)
- [ ] ✅ **Чек-лист**: каждый файл проверен по `development-checklist.md`

#### Как проверять:
```javascript
// В браузерной консоли
import { useModal } from './composables/ui/useModal.js'
const modal = useModal()
console.log(modal) // должно показать { isOpen, open, close }
```

---

### ✅ **ЭТАП 3: Перенос API слоя**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Переносим `useTasks-fixed.js` в `composables/api/useTasks.js`
- Применяем принцип SRP - разделяем loading состояния
- Убираем общий `loading` на отдельные: `createLoading`, `updateLoading`, `getLoading`

#### Файлы:
- `resources/js/composables/api/useTasks.js` (перенос + рефакторинг)

#### Критерии проверки:
- [ ] Файл перенесен
- [ ] Каждая операция имеет свой loading: `createLoading`, `updateLoading`, `getLoading`, `deleteLoading`
- [ ] Общий `loading` убран
- [ ] Все API методы работают
- [ ] ✅ **Чек-лист**: функции <20 строк, composable <5 экспортов

#### Как проверять:
```javascript
const { createLoading, updateLoading, getLoading, createTask, getTask } = useTasks()
console.log({ createLoading, updateLoading, getLoading }) // должны быть отдельные ref
```

---

### ✅ **ЭТАП 4: Создание Features слоя**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Создаем `composables/features/useTasksList.js` - логика списка задач
- Создаем `composables/features/useTaskModal.js` - логика модальных окон

#### Файлы:
- `resources/js/composables/features/useTasksList.js`
- `resources/js/composables/features/useTaskModal.js`

#### Критерии проверки useTasksList:
- [ ] Изолированное состояние: `tasks`, `loading`, `filters`
- [ ] Методы: `loadTasks()`, `applyFilters()`, `clearFilters()`
- [ ] НЕ содержит логику модалок
- [ ] ✅ **Чек-лист**: функции <20 строк, composable <5 экспортов

#### Критерии проверки useTaskModal:
- [ ] Изолированное состояние: `task`, `loading`, `isOpen`, `mode`
- [ ] Методы: `openViewModal()`, `openEditModal()`, `closeModal()`, `saveTask()`
- [ ] НЕ содержит логику списка  
- [ ] ✅ **Чек-лист**: функции <20 строк, composable <5 экспортов

#### Как проверять:
```javascript
const tasksList = useTasksList()
const taskModal = useTaskModal()

// Состояния не пересекаются
console.log(tasksList.loading !== taskModal.loading) // должно быть true
```

---

### ✅ **ЭТАП 5: Рефакторинг TaskManager.vue**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Убираем всю логику из TaskManager.vue
- Заменяем на композицию из composables
- Применяем принцип "явное лучше неявного"

#### Файлы:
- `resources/js/Components/TaskManager.vue` (рефакторинг)

#### Критерии проверки:
- [ ] В `<script setup>` только композиция composables
- [ ] Никакой бизнес-логики в компоненте
- [ ] Явные вызовы `tasksList.loadTasks()` после изменений
- [ ] Разделенные loading состояния в UI
- [ ] ✅ **Чек-лист**: компонент <200 строк, функции <20 строк

#### Как проверять:
- [ ] Открытие модалки просмотра НЕ обновляет список
- [ ] Сохранение задачи ЯВНО обновляет список
- [ ] Создание задачи ЯВНО обновляет список
- [ ] Каждое действие имеет свой loading индикатор

---

### ✅ **ЭТАП 6: Создание shared компонентов**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Создаем `components/shared/Button.vue` - универсальная кнопка
- Создаем `components/shared/LoadingSpinner.vue` - спиннер загрузки

#### Файлы:
- `resources/js/Components/shared/Button.vue`
- `resources/js/Components/shared/LoadingSpinner.vue`

#### Критерии проверки:
- [ ] Button.vue поддерживает props: `variant`, `size`, `loading`
- [ ] LoadingSpinner.vue поддерживает props: `size`
- [ ] Компоненты переиспользуемые и атомарные
- [ ] ✅ **Чек-лист**: проверены по `development-checklist.md`

#### Как проверять:
```vue
<Button variant="primary" size="md" :loading="false">Тест</Button>
<LoadingSpinner size="sm" />
```

---

### ✅ **ЭТАП 7: Создание Vue компонентов для задач**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Создаем `components/tasks/TasksTable.vue` - таблица задач
- Создаем `components/tasks/TaskRow.vue` - строка задачи
- Создаем `components/tasks/TaskForm.vue` - форма задачи

#### Файлы:
- `resources/js/Components/tasks/TasksTable.vue`
- `resources/js/Components/tasks/TaskRow.vue`
- `resources/js/Components/tasks/TaskForm.vue`

#### Критерии проверки:
- [ ] TasksTable принимает массив задач как props
- [ ] TaskRow эмитит события `@view`, `@edit`
- [ ] TaskForm работает в режимах создания/редактирования
- [ ] ✅ **Чек-лист**: функции <20 строк, компоненты <200 строк

---

### ✅ **ЭТАП 8: Миграция с HTML на Vue компоненты**
**Статус: ✅ ВЫПОЛНЕН**

#### Что делаем:
- Убираем `v-html="tasksHtml"` из TaskManager.vue
- Заменяем на `<TasksTable :tasks="tasks" />`
- Переходим с серверного HTML на реактивные Vue объекты

#### Критерии проверки:
- [ ] Убран `v-html` из шаблона
- [ ] Используются Vue компоненты вместо HTML строк
- [ ] API возвращает JSON объекты, а не HTML
- [ ] Полная реактивность данных

---

### ✅ **ЭТАП 9: Финальное тестирование и оптимизация**
**Статус: ✅ ВЫПОЛНЕН**

#### Что тестируем:
- [ ] Создание задачи работает
- [ ] Редактирование задачи работает  
- [ ] Просмотр задачи работает
- [ ] Фильтры работают
- [ ] Уведомления работают
- [ ] Нет множественных API вызовов
- [ ] Список обновляется только при необходимости

---

## 📝 Журнал выполнения

### ✅ Выполненные этапы:
- ✅ **ЭТАП 1** - Создание базовой структуры папок и utils
- ✅ **ЭТАП 2** - Создание базового UI слоя 
- ✅ **ЭТАП 3** - Перенос API слоя
- ✅ **ЭТАП 4** - Создание Features слоя
- ✅ **ЭТАП 5** - Рефакторинг TaskManager.vue
- ✅ **ЭТАП 6** - Создание shared компонентов
- ✅ **ЭТАП 7** - Создание Vue компонентов для задач
- ✅ **ЭТАП 8** - Миграция с HTML на Vue компоненты
- ✅ **ЭТАП 9** - Финальное тестирование и оптимизация

### 🎉 Статус проекта:
- **РЕФАКТОРИНГ ПОЛНОСТЬЮ ЗАВЕРШЕН!** 🚀

### 📋 Чек-лист проверок:
После каждого этапа пользователь проверяет:
1. Работоспособность функционала
2. Соответствие критериям
3. Отсутствие регрессий
4. Дает добро на следующий этап

---

## 🚨 Правила безопасности:
1. **Каждый этап** = отдельный коммит
2. **Проверка работоспособности** после каждого этапа
3. **Откат возможен** на любом этапе
4. **Не ломать** существующий функционал

---

## 📞 Процедура проверки:
1. Клод выполняет этап
2. Клод отмечает статус как "✅ ВЫПОЛНЕН"
3. Пользователь тестирует функционал
4. Пользователь дает добро на следующий этап
5. Переходим к следующему этапу

**Готов начинать с ЭТАПА 1?** 🚀