<template>
  <a-dropdown>
    <a-button type="text" class="ant-dropdown-link">
      {{ user?.name || 'User' }}
      <DownOutlined />
    </a-button>
    <template #overlay>
      <a-menu>
        <template
          v-for="item in getVisibleProfileItems"
          :key="item.key || 'divider'"
        >
          <!-- Divider -->
          <a-menu-divider v-if="item.type === 'divider'" />
          
          <!-- Regular menu item -->
          <a-menu-item v-else :key="item.key">
            <Link
              :href="item.href"
              :method="item.method || 'get'"
              :as="item.method === 'post' ? 'button' : 'a'"
              :class="item.method === 'post' ? 'w-full text-left' : ''"
              class="flex items-center"
            >
              <component
                v-if="item.icon"
                :is="iconComponents[item.icon]"
                class="mr-2"
              />
              {{ item.label }}
            </Link>
          </a-menu-item>
        </template>
      </a-menu>
    </template>
  </a-dropdown>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { DownOutlined } from '@ant-design/icons-vue'
import { useNavigation } from '../../composables/navigation/useNavigation.js'
import {
  DashboardOutlined,
  FileTextOutlined,
  ProjectOutlined,
  TagOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined
} from '@ant-design/icons-vue'

// Icons mapping
const iconComponents = {
  DashboardOutlined,
  FileTextOutlined,
  ProjectOutlined,
  TagOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined
}

// Page data
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Use navigation composable
const { getVisibleProfileItems } = useNavigation()
</script>