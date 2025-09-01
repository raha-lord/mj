<template>
  <a-breadcrumb v-if="getBreadcrumbs.length > 1" class="mb-4">
    <a-breadcrumb-item
      v-for="(breadcrumb, index) in getBreadcrumbs"
      :key="index"
    >
      <Link
        v-if="breadcrumb.href && index < getBreadcrumbs.length - 1"
        :href="breadcrumb.href"
        class="text-gray-600 hover:text-blue-600"
      >
        {{ breadcrumb.title }}
      </Link>
      <span v-else class="text-gray-900 font-medium">
        {{ breadcrumb.title }}
      </span>
    </a-breadcrumb-item>
  </a-breadcrumb>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useNavigation } from '../../composables/navigation/useNavigation.js'

// Use navigation composable
const { getBreadcrumbs } = useNavigation()
</script>