<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="space-y-4">
      <!-- Заголовок -->
      <div class="flex items-center justify-between">
        <Skeleton width="200px" height="20px" />
        <Skeleton width="80px" height="24px" rounded="full" />
      </div>
      
      <!-- Контент -->
      <div class="space-y-3">
        <Skeleton width="100%" height="16px" />
        <Skeleton width="85%" height="16px" />
        <Skeleton width="70%" height="16px" />
      </div>
      
      <!-- Метаданные -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
          <Skeleton variant="circular" width="32px" />
          <Skeleton width="100px" height="14px" />
        </div>
        <Skeleton width="60px" height="14px" />
      </div>
    </div>
  </div>
</template>

<script setup>
import Skeleton from './Skeleton.vue'
</script>