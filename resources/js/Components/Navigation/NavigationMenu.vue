<template>
  <a-menu
    mode="horizontal"
    :selectedKeys="[getCurrentRoute]"
    class="flex-1 border-none bg-transparent ml-10"
  >
    <a-menu-item
      v-for="item in getVisibleItems"
      :key="item.key"
    >
      <Link :href="item.href" class="flex items-center">
        <component
          v-if="item.icon"
          :is="iconComponents[item.icon]"
          class="mr-2"
        />
        {{ item.label }}
        <a-badge
          v-if="item.badge"
          :count="item.badge.count"
          :color="item.badge.color"
          class="ml-2"
        />
      </Link>
    </a-menu-item>
  </a-menu>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useNavigation } from '../../composables/navigation/useNavigation.js'
import {
  DashboardOutlined,
  FileTextOutlined,
  ProjectOutlined,
  TagOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined,
  TeamOutlined
} from '@ant-design/icons-vue'

// Icons mapping
const iconComponents = {
  DashboardOutlined,
  FileTextOutlined,
  ProjectOutlined,
  TagOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined,
  TeamOutlined
}

// Use navigation composable
const { getCurrentRoute, getVisibleItems } = useNavigation()
</script>