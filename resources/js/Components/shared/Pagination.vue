<template>
  <div v-if="pagination && pagination.total > 0" class="flex items-center justify-between bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
    <!-- Информация о записях -->
    <div class="flex-1 flex justify-between sm:hidden">
      <Button
        v-if="pagination.current_page > 1"
        @click="goToPage(pagination.current_page - 1)"
        variant="secondary"
        size="sm"
      >
        Предыдущая
      </Button>
      <Button
        v-if="pagination.current_page < pagination.last_page"
        @click="goToPage(pagination.current_page + 1)"
        variant="secondary"
        size="sm"
      >
        Следующая
      </Button>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700 dark:text-gray-300">
          Показано 
          <span class="font-medium">{{ pagination.from }}</span>
          -
          <span class="font-medium">{{ pagination.to }}</span>
          из
          <span class="font-medium">{{ pagination.total }}</span>
          задач
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <!-- Кнопка "Предыдущая" -->
        <Button
          :disabled="pagination.current_page <= 1"
          @click="goToPage(pagination.current_page - 1)"
          variant="secondary"
          size="sm"
        >
          <template #icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </template>
        </Button>

        <!-- Страницы -->
        <div class="flex space-x-1">
          <!-- Первая страница -->
          <button
            v-if="showFirstPage"
            @click="goToPage(1)"
            class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            1
          </button>

          <!-- Троеточие слева -->
          <span v-if="showLeftEllipsis" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
            ...
          </span>

          <!-- Видимые страницы -->
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>

          <!-- Троеточие справа -->
          <span v-if="showRightEllipsis" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
            ...
          </span>

          <!-- Последняя страница -->
          <button
            v-if="showLastPage"
            @click="goToPage(pagination.last_page)"
            class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            {{ pagination.last_page }}
          </button>
        </div>

        <!-- Кнопка "Следующая" -->
        <Button
          :disabled="pagination.current_page >= pagination.last_page"
          @click="goToPage(pagination.current_page + 1)"
          variant="secondary"
          size="sm"
        >
          <template #icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </template>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  pagination: {
    type: Object,
    required: true
  },
  maxVisiblePages: {
    type: Number,
    default: 5
  }
})

const emit = defineEmits(['page-changed'])

// Вычисляемые свойства для пагинации
const visiblePages = computed(() => {
  const current = props.pagination.current_page
  const total = props.pagination.last_page
  const maxVisible = props.maxVisiblePages
  
  let start = Math.max(1, current - Math.floor(maxVisible / 2))
  let end = Math.min(total, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  const pages = []
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const showFirstPage = computed(() => {
  return !visiblePages.value.includes(1) && props.pagination.last_page > 1
})

const showLastPage = computed(() => {
  return !visiblePages.value.includes(props.pagination.last_page) && props.pagination.last_page > 1
})

const showLeftEllipsis = computed(() => {
  return visiblePages.value[0] > 2
})

const showRightEllipsis = computed(() => {
  return visiblePages.value[visiblePages.value.length - 1] < props.pagination.last_page - 1
})

// Методы
const goToPage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page && page !== props.pagination.current_page) {
    emit('page-changed', page)
  }
}
</script>