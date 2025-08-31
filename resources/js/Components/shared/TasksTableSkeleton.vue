<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Задача
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Проект
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Статус
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Приоритет
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Размер
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Действия
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-for="n in rows" :key="n" class="animate-pulse">
          <!-- Задача -->
          <td class="px-6 py-4">
            <div class="flex flex-col space-y-2">
              <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-48 animate-pulse"></div>
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32 animate-pulse"></div>
            </div>
          </td>
          
          <!-- Проект -->
          <td class="px-6 py-4">
            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-24 animate-pulse"></div>
          </td>
          
          <!-- Статус -->
          <td class="px-6 py-4">
            <div class="h-6 bg-gray-300 dark:bg-gray-600 rounded-full w-20 animate-pulse"></div>
          </td>
          
          <!-- Приоритет -->
          <td class="px-6 py-4">
            <div class="h-6 bg-gray-300 dark:bg-gray-600 rounded-full w-16 animate-pulse"></div>
          </td>
          
          <!-- Размер -->
          <td class="px-6 py-4">
            <div class="h-6 bg-gray-300 dark:bg-gray-600 rounded-full w-10 animate-pulse"></div>
          </td>
          
          <!-- Действия -->
          <td class="px-6 py-4">
            <div class="flex items-center space-x-2">
              <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-20 animate-pulse"></div>
              <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-20 animate-pulse"></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  rows: {
    type: Number,
    default: 5
  }
})
</script>