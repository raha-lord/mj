<template>
  <a-breadcrumb v-if="getBreadcrumbs.length > 1" class="mb-4">
    <a-breadcrumb-item
        v-for="(breadcrumb, index) in getBreadcrumbs"
        :key="index"
    >
      <Link
          v-if="breadcrumb.href && breadcrumb.href !== '#' && index < getBreadcrumbs.length - 1"
          :href="breadcrumb.href"
          class="breadcrumb-link"
      >
        <template v-if="breadcrumb.isOrganization">
          <TeamOutlined class="mr-1" />
        </template>
        {{ breadcrumb.title }}
      </Link>
      <span v-else class="breadcrumb-current">
        <template v-if="breadcrumb.isOrganization">
          <TeamOutlined class="mr-1 text-blue-600" />
        </template>
        {{ breadcrumb.title }}
      </span>
    </a-breadcrumb-item>
  </a-breadcrumb>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { TeamOutlined } from '@ant-design/icons-vue'
import { useNavigation } from '../../composables/navigation/useNavigation.js'

// Use navigation composable
const { getBreadcrumbs } = useNavigation()
</script>