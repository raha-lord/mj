  # План миграции на Inertia.js

## Обзор миграции

### Что у нас есть сейчас:
- Laravel 10 + Blade views
- Vue 3 компоненты монтируются в отдельные div'ы
- Данные передаются через `window.taskManagerInitialData`
- Полная перезагрузка страниц при навигации
- Ant Design Vue для UI компонентов
- Система тем через отдельные CSS файлы

### Что получим после миграции:
- Laravel backend + Inertia.js + Vue 3 frontend
- SPA навигация без перезагрузки страниц
- Автоматическая передача данных как Vue props
- Единый layout для всего приложения
- Сохранение всех существующих компонентов и функций

## ⚠️ ВАЖНО: Стратегия UI компонентов

**🎯 ВСЕ СТРАНИЦЫ ДЕЛАЕМ СРАЗУ НА ANT DESIGN - НЕ НАТИВНО!**

**Причины:**
- У нас уже есть рабочий TaskManager на Ant Design
- Система тем настроена под Ant Design
- Не делаем двойную работу (сначала нативно, потом переписываем)
- Консистентный дизайн во всем приложении
- Готовые компоненты: таблицы, формы, валидация

**Компоненты для использования:**
- `<a-form>`, `<a-form-item>` - все формы
- `<a-input>`, `<a-select>`, `<a-button>` - поля ввода
- `<a-table>` - таблицы данных
- `<a-modal>` - модальные окна
- `<a-layout>`, `<a-menu>` - основная структура

## 📋 **ПРАВИЛА РАЗРАБОТКИ**

### **🎯 ОБЯЗАТЕЛЬНО следовать development-checklist.md:**
- **SRP**: Одна функция = одна задача (макс 20 строк)
- **Разделение состояний**: каждый компонент изолирован
- **Слоистая архитектура**: API → Features → UI → Utils
- **Явные зависимости**: никакой магии, только explicit imports
- **Композиция**: строить сложное из простых блоков

### **🎨 СТИЛИЗАЦИЯ - ТОЛЬКО В CSS ФАЙЛАХ:**
- **❌ НЕ ПИСАТЬ стили в Vue компонентах (`<style>`)**
- **✅ ИСПОЛЬЗОВАТЬ отдельные CSS файлы**
- **✅ СИСТЕМА ТЕМ через отдельные CSS файлы**
- **✅ TAILWIND для utility классов**
- **✅ ANT DESIGN переменные в CSS файлах**

### **📁 Структура стилей:**
```
/public/css/
├── themes/
│   ├── green.css    # Тема зеленая
│   ├── blue.css     # Тема синяя  
│   └── purple.css   # Тема фиолетовая
├── components/      # Стили компонентов
└── pages/          # Стили страниц
```

## 🚨 **ВАЛИДАЦИЯ И ОБРАБОТКА ОШИБОК**

### **📝 Централизованный подход Inertia + Ant Design:**

#### **useFormErrors composable (UI слой):**
```javascript
// composables/ui/useFormErrors.js
export function useFormErrors(form) {
  const getFieldError = (field) => {
    return form.errors[field] ? {
      validateStatus: 'error',
      help: form.errors[field]
    } : {}
  }
  
  const hasErrors = computed(() => Object.keys(form.errors).length > 0)
  
  const clearFieldError = (field) => {
    if (form.errors[field]) {
      delete form.errors[field]
    }
  }
  
  return { getFieldError, hasErrors, clearFieldError }
}
```

#### **Использование в формах:**
```vue
<template>
  <a-form @submit="handleSubmit">
    <a-form-item 
      label="Название" 
      v-bind="getFieldError('name')"
    >
      <a-input 
        v-model:value="form.name" 
        @input="clearFieldError('name')"
      />
    </a-form-item>
  </a-form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { useFormErrors } from '@/composables/ui/useFormErrors'

const form = useForm({ name: '' })
const { getFieldError, clearFieldError } = useFormErrors(form)
</script>
```

---

## Этап 1: Подготовка и установка

### 1.0 Переименование legacy файлов ✅ ВЫПОЛНЕНО
```bash
# Переименовали старые директории
mv resources/js → resources/js-legacy
mv resources/views → resources/views-legacy

# Создали новые для Inertia
mkdir resources/js      # Новый Inertia код
mkdir resources/views   # Только app.blade.php
```

**Структура после переименования:**
```
/resources/
├── js-legacy/          # Старый Vue + Blade код
├── views-legacy/       # Старые Blade views
├── js/                 # НОВЫЙ Inertia код (пустой)
└── views/              # НОВЫЙ Inertia views (пустой)
```

### 1.1 Установка пакетов ✅ ВЫПОЛНЕНО
```bash
# Laravel пакет
composer require inertiajs/inertia-laravel  ✅

# Vue пакеты  
npm install @inertiajs/vue3  ✅

# Дополнительные зависимости (позже)
# composer require tightenco/ziggy  # Для роутов в JS
```

### 1.2 Публикация конфигурации ✅ ВЫПОЛНЕНО
```bash
php artisan inertia:middleware  ✅
# php artisan vendor:publish --provider="Inertiajs\ServiceProvider"  # Не нужно
```

### 1.3 Регистрация middleware ✅ ВЫПОЛНЕНО
- ✅ Добавлен `HandleInertiaRequests` в `app/Http/Kernel.php` (web группа)
- ✅ Настроен shared data (пользователь, флеш сообщения, ошибки валидации)

---

## Этап 2: Настройка архитектуры

### 2.1 Создание базового Blade template ✅ ВЫПОЛНЕНО
- ✅ Создан `resources/views/app.blade.php` - единственный Blade файл
- ✅ Подключен Vite assets
- ✅ Добавлены мета теги и SEO
- ✅ Добавлена директива `@inertia`

### 2.2 Создание структуры файлов ✅ ВЫПОЛНЕНО
```
/resources/js/
├── Layouts/
│   ├── AppLayout.vue          # Основной layout
│   └── AuthLayout.vue         # Layout для авторизации (если нужен)
├── Pages/
│   ├── Auth/
│   │   ├── Login.vue          # Вход в систему
│   │   ├── Register.vue       # Регистрация
│   │   ├── ForgotPassword.vue # Восстановление пароля
│   │   ├── ConfirmPassword.vue# Подтверждение пароля
│   │   └── VerifyEmail.vue    # Подтверждение email
│   ├── Tasks/
│   │   ├── Index.vue          # Список задач (текущий TaskManager)
│   │   ├── Show.vue           # Просмотр задачи
│   │   ├── Create.vue         # Создание задачи
│   │   └── Edit.vue           # Редактирование задачи
│   ├── Projects/
│   │   ├── Index.vue          # Список проектов
│   │   ├── Show.vue           # Просмотр проекта
│   │   ├── Create.vue         # Создание проекта
│   │   └── Edit.vue           # Редактирование проекта
│   ├── Statuses/
│   │   ├── Index.vue          # Список статусов
│   │   ├── Show.vue           # Просмотр статуса
│   │   ├── Create.vue         # Создание статуса
│   │   └── Edit.vue           # Редактирование статуса
│   ├── Profile/
│   │   ├── Show.vue           # Профиль пользователя
│   │   └── Edit.vue           # Редактирование профиля
│   ├── Dashboard.vue          # Главная страница
│   ├── Home.vue               # Домашняя страница
│   └── Welcome.vue            # Стартовая страница
├── Components/
│   ├── TaskManager.vue        # Переделаем под новую архитектуру
│   ├── TaskModal.vue          # Оставляем как есть
│   └── shared/
│       ├── ThemeSelector.vue  # Оставляем как есть
│       └── ...                # Остальные компоненты
├── Composables/
│   ├── api/                   # API слой
│   │   ├── useTasks.js        # API для задач
│   │   ├── useProjects.js     # API для проектов
│   │   └── useAuth.js         # API для авторизации
│   ├── features/              # Бизнес-логика
│   │   ├── useTasksList.js    # Логика списка задач
│   │   ├── useTaskModal.js    # Логика модалки задач
│   │   └── useProjectsList.js # Логика списка проектов
│   ├── ui/                    # UI логика
│   │   ├── useNotifications.js # Уведомления
│   │   ├── useFormErrors.js   # Обработка ошибок форм
│   │   ├── useGlobalErrors.js # Глобальные ошибки
│   │   └── useModal.js        # Базовая логика модалок
│   └── shared/                # Общие composables
└── utils/
    ├── themeManager.js        # Оставляем как есть
    ├── errorHandler.js        # Утилиты обработки ошибок
    └── validation.js          # Валидация
```

### 2.3 Главный layout (AppLayout.vue) ✅ ВЫПОЛНЕНО
**🎯 ИСПОЛЬЗОВАТЬ ANT DESIGN: `<a-layout>`, `<a-menu>`, `<a-dropdown>`**

- ✅ **Навигационное меню** - создано с Ant Design
  - ✅ Логотип приложения  
  - ✅ Основные разделы (Dashboard, Tasks, Projects, Statuses)
  - ✅ **Компоненты**: `<a-menu>`, `<a-menu-item>`
- ✅ **Header с пользователем**
  - ✅ Dropdown профиль пользователя → `<a-dropdown>`
  - ✅ Кнопка выхода → `<a-button>`
  - ✅ Интеграция ThemeSelector  
- 🔄 **Респонсивное меню** для мобильных → `<a-drawer>` (следующая итерация)
- 🔄 **Footer** (если нужен)
- ✅ **Интеграция уведомлений** → GlobalErrorHandler компонент
- 🔄 **Breadcrumbs навигация** → `<a-breadcrumb>` (следующая итерация)

### 2.4 Layout для авторизации (AuthLayout.vue)  
- 🔄 Простой layout для страниц авторизации (следующий этап)
- 🔄 Без навигационного меню
- 🔄 Центрированные формы
- 🔄 Минимальный дизайн

### 2.5 Обновление app.js ✅ ВЫПОЛНЕНО
- ✅ Настройка Inertia App
- ✅ Подключение Ant Design  
- ✅ Интеграция themeManager
- ✅ Настройка глобальных компонентов

---

## Этап 3: Миграция backend

### 3.1 Настройка shared data ✅ ВЫПОЛНЕНО
- ✅ HandleInertiaRequests middleware настроен
- ✅ Shared data: auth.user, appName, flash messages, errors

### 3.2 Создание тестовой Dashboard страницы ✅ ВЫПОЛНЕНО
- ✅ Pages/Dashboard.vue с Ant Design компонентами
- ✅ HomeController обновлен для Inertia
- ✅ Роуты обновлены для тестирования
- ✅ Приложение собирается успешно

### 3.3 Тестирование базовой настройки 🔄 ЧАСТИЧНО
- ✅ Vite сборка успешна (Dashboard.js генерируется)
- ✅ Роуты зарегистрированы корректно  
- 🔄 HTTP доступ (502 ошибка - требует настройки nginx/auth)

### 3.4 Обновление контроллеров ✅ ЧАСТИЧНО
- ✅ **HomeController** → Dashboard страница готова
- ✅ **TaskController** → index() метод для Inertia
- ✅ **AuthController** → создан для замены Livewire

---

## Этап 4: Миграция критически важных страниц ✅ ВЫПОЛНЕНО

### 4.1 AuthLayout.vue ✅ ВЫПОЛНЕНО
- ✅ Простой layout для страниц авторизации
- ✅ Центрированные формы с Ant Design Card
- ✅ Интеграция ThemeSelector
- ✅ Flash messages отображение
- ✅ GlobalErrorHandler интеграция

### 4.2 Auth/Login.vue ✅ ВЫПОЛНЕНО
**🎯 ИСПОЛЬЗОВАТЬ ANT DESIGN ФОРМЫ: `<a-form>`, `<a-input>`, `<a-button>`**

- ✅ Форма входа в систему → `<a-form>`
- ✅ Валидация логина/пароля → `<a-form-item>` с правилами
- ✅ Запомнить меня → `<a-checkbox>`
- ✅ Ссылки на регистрацию и восстановление → `<a-button type="link">`
- ✅ Интеграция useFormErrors для отображения ошибок
- ✅ Обработка Inertia form submission

### 4.3 Auth роуты ✅ ВЫПОЛНЕНО
- ✅ Создан AuthController для замены Livewire Volt
- ✅ Настроены роуты: GET /login, POST /login, POST /logout
- ✅ Middleware guest/auth настроены корректно

### 4.4 Tasks/Index.vue ✅ ВЫПОЛНЕНО
**🎯 СОБЛЮДАТЬ ВСЕ ПРАВИЛА из development-checklist.md**

- ✅ Перенесен функционал из legacy TaskManager.vue
- ✅ Получает данные через props вместо window
- ✅ Использует AppLayout
- ✅ Сохранен весь функционал (таблица, фильтры)
- ✅ Ant Design компоненты: `<a-table>`, `<a-card>`, `<a-select>`
- ✅ Инерция навигация и фильтрация

### 4.5 TaskController обновление ✅ ВЫПОЛНЕНО
- ✅ index() метод использует Inertia::render()
- ✅ Возвращает структурированные данные для Vue
- ✅ Поддержка пагинации и фильтрации
- ✅ Интеграция с существующим TaskService

### 4.6 Сборка и тестирование ✅ ВЫПОЛНЕНО
- ✅ Vite успешно генерирует все страницы
- ✅ Code splitting работает (Login.js, Dashboard.js, Tasks/Index.js)
- ✅ Ant Design стили подключены
- ✅ Система тем интегрирована

### 3.1 Создание базового Blade template
- `resources/views/app.blade.php` - единственный Blade файл
- Подключение Vite assets
- Мета теги и SEO
- Директива `@inertia`

### 3.2 Обновление контроллеров

#### TaskController
```php
// Было
public function index() {
    return view('tasks.index', [...]);
}

// Станет
public function index() {
    return Inertia::render('Tasks/Index', [
        'tasks' => Task::with(['project', 'status', 'size'])->get(),
        'projects' => Project::all(),
        'statuses' => Status::all(),
        'sizes' => Size::all(),
        'users' => User::all(),
    ]);
}

public function show(Task $task) {
    return Inertia::render('Tasks/Show', [
        'task' => $task->load(['project', 'status', 'size', 'user']),
    ]);
}

public function create() {
    return Inertia::render('Tasks/Create', [
        'projects' => Project::all(),
        'statuses' => Status::all(),
        'sizes' => Size::all(),
        'users' => User::all(),
    ]);
}

public function edit(Task $task) {
    return Inertia::render('Tasks/Edit', [
        'task' => $task,
        'projects' => Project::all(),
        'statuses' => Status::all(),
        'sizes' => Size::all(),
        'users' => User::all(),
    ]);
}
```

#### ProjectController
```php
public function index() {
    return Inertia::render('Projects/Index', [
        'projects' => Project::with('tasks')->get(),
    ]);
}

public function show(Project $project) {
    return Inertia::render('Projects/Show', [
        'project' => $project->load('tasks.status'),
    ]);
}

public function create() {
    return Inertia::render('Projects/Create');
}

public function edit(Project $project) {
    return Inertia::render('Projects/Edit', [
        'project' => $project,
    ]);
}
```

#### StatusController
```php
public function index() {
    return Inertia::render('Statuses/Index', [
        'statuses' => Status::withCount('tasks')->get(),
    ]);
}

public function show(Status $status) {
    return Inertia::render('Statuses/Show', [
        'status' => $status->load('tasks'),
    ]);
}

public function create() {
    return Inertia::render('Statuses/Create');
}

public function edit(Status $status) {
    return Inertia::render('Statuses/Edit', [
        'status' => $status,
    ]);
}
```

#### AuthController (Livewire → Inertia)
```php
// Миграция с Livewire на обычные контроллеры
public function showLogin() {
    return Inertia::render('Auth/Login');
}

public function showRegister() {
    return Inertia::render('Auth/Register');
}

public function showForgotPassword() {
    return Inertia::render('Auth/ForgotPassword');
}

public function showVerifyEmail() {
    return Inertia::render('Auth/VerifyEmail');
}
```

#### Другие контроллеры
- **DashboardController** → `Inertia::render('Dashboard')`
- **HomeController** → `Inertia::render('Home')`  
- **ProfileController** → `Inertia::render('Profile/Show')`

### 3.3 Обновление роутов
- Убрать разделение на web/api роуты
- Создать RESTful роуты для Inertia
- Настроить middleware для аутентификации

---

## Этап 4: Миграция frontend страниц

### 4.1 Миграция страниц задач
#### Pages/Tasks/Index.vue
**🎯 СОБЛЮДАТЬ ВСЕ ПРАВИЛА из development-checklist.md**

- Перенести логику из текущего TaskManager.vue
- Получать данные через props вместо window
- Использовать AppLayout
- Сохранить всю существующую функциональность (таблица, фильтры, модальные окна)
- **❌ НЕ ПИСАТЬ стили в `<style>` - только CSS файлы**
- **✅ РАЗБИТЬ на composables**: useTasksList, useTaskModal, useTaskFilters
- **✅ SRP**: каждый composable = одна ответственность

#### Pages/Tasks/Show.vue
- Создать страницу просмотра задачи
- Отобразить все поля задачи
- Кнопки редактирования и удаления
- Breadcrumbs навигация

#### Pages/Tasks/Create.vue & Edit.vue
- Формы создания/редактирования
- Валидация через Inertia
- Интеграция с Ant Design формами

### 4.2 Миграция страниц проектов
#### Pages/Projects/Index.vue
- Таблица проектов
- Фильтрация и поиск
- Ссылки на просмотр проекта

#### Pages/Projects/Show.vue  
- Информация о проекте
- Список связанных задач
- Статистика по проекту

#### Pages/Projects/Create.vue & Edit.vue
- Формы управления проектами
- Валидация полей

### 4.3 Миграция страниц статусов
#### Pages/Statuses/Index.vue
- Управление статусами задач
- CRUD операции
- Счетчики задач по статусам

#### Pages/Statuses/Show.vue
- Просмотр статуса
- Связанные задачи

### 4.4 Миграция страниц авторизации
**🎯 ИСПОЛЬЗОВАТЬ ANT DESIGN ФОРМЫ: `<a-form>`, `<a-input>`, `<a-button>`**

#### Pages/Auth/Login.vue
- Форма входа в систему → `<a-form>`
- Валидация логина/пароля → `<a-form-item>` с правилами
- Запомнить меня → `<a-checkbox>`  
- Ссылки на регистрацию и восстановление → `<a-button type="link">`

#### Pages/Auth/Register.vue  
- Форма регистрации → `<a-form>`
- Валидация полей → `<a-form-item>` с правилами
- Подтверждение пароля → `<a-input type="password">`

#### Pages/Auth/ForgotPassword.vue
- Восстановление пароля по email → `<a-form>` + `<a-input>`  
- Отправка ссылки восстановления → `<a-button type="primary">`

#### Pages/Auth/VerifyEmail.vue
- Подтверждение email адреса → `<a-result>` компонент
- Повторная отправка письма → `<a-button>`

### 4.5 Остальные страницы
#### Pages/Dashboard.vue
- Главная панель
- Виджеты и статистика
- Быстрые действия

#### Pages/Home.vue
- Домашняя страница
- Возможно, дублирует Dashboard

#### Pages/Profile/Show.vue & Edit.vue
- Профиль пользователя
- Редактирование данных
- Смена пароля (интеграция с существующими Livewire компонентами)

#### Pages/Welcome.vue
- Стартовая страница для неавторизованных
- Возможно, landing page

### 4.5 Обновление навигации
- Использовать Inertia Link компоненты
- Настроить активные состояния меню
- Добавить индикаторы загрузки
- Breadcrumbs для всех разделов

---

## Этап 5: Формы и модальные окна
**🎯 ВСЕ ФОРМЫ НА ANT DESIGN! Интеграция с Inertia.js**

### 5.1 Обновление TaskModal
- Интеграция с Inertia forms → `useForm()` composable
- Обработка валидации от Laravel → `<a-form-item>` с ошибками  
- Сохранение существующего UI → `<a-modal>`, `<a-form>`

**Пример интеграции:**
```vue
<a-modal v-model:open="isOpen">
  <a-form @submit="form.post('/tasks')">
    <a-form-item label="Название" :help="form.errors.name">
      <a-input v-model:value="form.name" />
    </a-form-item>
  </a-form>
</a-modal>
```

### 5.2 Создание отдельных страниц для CRUD
- Pages/Tasks/Create.vue → `<a-form>` с полной валидацией
- Pages/Tasks/Edit.vue → предзаполненная `<a-form>`
- Pages/Tasks/Show.vue → `<a-descriptions>` для отображения
- Или оставить модальные окна (на выбор)

### 5.3 Централизованная обработка ошибок
**🚨 ОБЯЗАТЕЛЬНО создать систему обработки ошибок**

#### **composables/ui/useFormErrors.js**
- Интеграция Inertia errors с Ant Design
- Автоматическое отображение ошибок в `<a-form-item>`
- Функции очистки ошибок при изменении полей

#### **composables/ui/useGlobalErrors.js**  
- Обработка сетевых ошибок (500, 404, 422)
- Отображение через `<a-notification>`
- Интеграция с Laravel exception handler

#### **Примеры использования:**
```vue
<!-- В каждой форме -->
<a-form-item v-bind="getFieldError('name')">
  <a-input v-model:value="form.name" @input="clearFieldError('name')" />
</a-form-item>

<!-- Глобальные ошибки в AppLayout -->
<template>
  <div>
    <GlobalErrorHandler />
    <slot />
  </div>
</template>
```

---

## Этап 6: Интеграция существующих систем

### 6.1 Система тем
- Перенести themeManager.js
- Интегрировать в AppLayout
- Сохранить все CSS файлы тем
- Обеспечить работу во всех страницах

### 6.2 Уведомления
**🎯 СЛЕДОВАТЬ ПРАВИЛАМ: UI слой отдельно**

- Интегрировать useNotifications в layout
- Настроить отображение Laravel flash messages
- Сохранить существующий функционал
- **✅ useNotifications** - только UI логика (composables/ui/)

### 6.3 Composables рефакторинг 
**🎯 ОБЯЗАТЕЛЬНО СОБЛЮДАТЬ СЛОИСТУЮ АРХИТЕКТУРУ**

- **API слой** (`composables/api/`): 
  - useTasks() - только API вызовы
  - useProjects() - только API вызовы  
- **Features слой** (`composables/features/`):
  - useTasksList() - бизнес-логика списка
  - useTaskModal() - бизнес-логика модалки
- **UI слой** (`composables/ui/`):
  - useNotifications() - только уведомления
- **✅ SRP**: каждый composable = одна ответственность
- **✅ FAIL FAST**: валидация параметров

---

## Этап 7: Роутинг и навигация

### 7.1 Настройка Ziggy
- Генерация JS роутов из Laravel
- Использование в Vue компонентах
- Типизация роутов (опционально)

### 7.2 Breadcrumbs и навигация
- Создание компонента Breadcrumbs
- Интеграция в AppLayout
- Автоматическое определение текущей страницы

---

## Этап 8: Оптимизация и улучшения

### 8.1 Code splitting
- Lazy loading для страниц
- Оптимизация bundle размера
- Prefetching для популярных страниц

### 8.2 SEO и мета теги
- Динамические title для страниц
- Meta описания
- Open Graph теги

### 8.3 Error handling
- 404 страницы через Inertia
- Обработка 500 ошибок
- Валидация форм

---

## Этап 9: Тестирование

### 9.1 Функциональное тестирование
- Проверка всех CRUD операций
- Тестирование форм и валидации
- Проверка навигации

### 9.2 UI тестирование
- Работа тем на всех страницах
- Адаптивность
- Совместимость с браузерами

### 9.3 Performance тестирование
- Скорость загрузки
- Размер bundle
- Memory leaks

---

## Этап 10: Деплой и финализация

### 10.1 Обновление сборки
- Настройка Vite для production
- Обновление CI/CD если есть
- Проверка assets

### 10.2 Документация
- Обновление README
- Документация по новой архитектуре
- Руководство для разработчиков

### 10.3 Откат план
- Backup текущей версии
- План возврата к старой архитектуре
- Мониторинг после деплоя

---

## Порядок выполнения

### Неделя 1: Подготовка и фундамент
- **Этапы 1-2**: Установка Inertia, создание архитектуры
- **Создание**: AppLayout, базовый app.blade.php
- **Настройка**: Middleware, shared data

### Неделя 2: Backend миграция
- **Этап 3**: Обновление всех контроллеров:
  - TaskController (приоритет)
  - ProjectController  
  - StatusController
  - DashboardController, HomeController, ProfileController
- **Роуты**: Настройка всех RESTful роутов

### Неделя 3: Основные страницы (Tasks + Projects)
- **Этап 4.1**: Миграция страниц задач
  - Pages/Tasks/Index.vue (основной приоритет)
  - Pages/Tasks/Show.vue, Create.vue, Edit.vue
- **Этап 4.2**: Миграция страниц проектов
  - Pages/Projects/Index.vue, Show.vue, Create.vue, Edit.vue

### Неделя 4: Остальные страницы + формы
- **Этап 4.3-4.4**: 
  - Pages/Statuses/* (все CRUD страницы)
  - Pages/Dashboard.vue, Home.vue
  - Pages/Profile/* (просмотр и редактирование)
  - Pages/Welcome.vue
- **Этап 5**: Интеграция форм и модальных окон

### Неделя 5: Системы и интеграция
- **Этап 6**: Интеграция существующих систем
  - Система тем (themeManager)
  - Уведомления
  - Composables обновление
- **Этап 4.5**: Навигация и меню
- **Этап 7**: Роутинг, breadcrumbs

### Неделя 6: Оптимизация и финализация  
- **Этап 8**: Оптимизация (code splitting, SEO)
- **Этапы 9-10**: Тестирование и деплой

---

## Приоритетность страниц

### 🔴 Критический приоритет (неделя 2-3)
1. **useFormErrors.js** - без этого формы не работают
2. **Auth/Login.vue** - авторизация критически важна
3. **AppLayout.vue** - навигационное меню с Livewire
4. **Dashboard.vue** - главная страница после входа

### 🔴 Высокий приоритет (неделя 3)  
1. **Tasks/Index.vue** - основная страница, уже есть Vue компонент
2. **Auth/Register.vue** - регистрация пользователей
3. **Tasks/Create.vue, Edit.vue** - основной функционал

### 🟡 Средний приоритет (неделя 4)
1. **Projects/Index.vue** - управление проектами
2. **Tasks/Show.vue** - просмотр задач  
3. **Auth/ForgotPassword.vue, VerifyEmail.vue** - остальная авторизация
4. **Projects/Show.vue, Create.vue, Edit.vue**

### 🟢 Низкий приоритет (неделя 4-5)
1. **Statuses/** - административные страницы
2. **Profile/** - профиль пользователя (есть Livewire компоненты)
3. **Home.vue, Welcome.vue** - статичные страницы

---

## Риски и их минимизация

### Технические риски
- **Поломка существующего функционала** → Поэтапная миграция, тщательное тестирование
- **Конфликты с Ant Design** → Тестирование интеграции на раннем этапе
- **Проблемы с темами** → Сохранение существующего подхода

### Пользовательские риски
- **Изменение UX** → Сохранение всех существующих функций
- **Скорость работы** → Мониторинг производительности
- **Совместимость браузеров** → Тестирование на разных браузерах

---

## Критерии успеха

✅ **Функциональные:**
- Все существующие функции работают
- SPA навигация работает корректно
- Формы сохраняют и валидируют данные
- Темы переключаются на всех страницах

✅ **Производительные:**
- Время загрузки не хуже текущего
- Переходы между страницами < 200мс
- Bundle размер оптимален

✅ **Пользовательские:**
- UI остается знакомым
- Новые возможности интуитивны
- Нет регрессий в функционале

✅ **Архитектурные (ОБЯЗАТЕЛЬНО):**
- **Соблюдены ВСЕ правила из development-checklist.md**
- **Слоистая архитектура**: API → Features → UI → Utils
- **SRP**: каждая функция/composable = одна ответственность
- **НИ ОДНОГО стиля в Vue компонентах - только CSS файлы**
- **Инверсия зависимостей**: компоненты зависят от composables
- **Явные импорты**: никакой магии

---

Готов начинать миграцию? С какого этапа начнем?