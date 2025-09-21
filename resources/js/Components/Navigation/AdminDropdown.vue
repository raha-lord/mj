<template>
  <a-dropdown v-if="showAdminDropdown" placement="bottomRight">
    <a-button 
      type="text" 
      class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
    >
      Управление
      <DownOutlined class="ml-1" />
    </a-button>

    <template #overlay>
      <a-menu>
        <!-- Мои организации -->
        <a-menu-item key="my-organizations">
          <Link href="/organizations/my" class="flex items-center">
            <TeamOutlined class="mr-2" />
            Мои организации
          </Link>
        </a-menu-item>

        <!-- Пользователи текущей организации -->
        <a-menu-item key="organization-users" v-if="currentOrganization">
          <Link :href="`/organizations/${currentOrganization.id}/users`" class="flex items-center">
            <UserOutlined class="mr-2" />
            Пользователи
          </Link>
        </a-menu-item>

        <!-- Настройки организации -->
        <a-menu-item key="organization-settings" v-if="currentOrganization && canManageOrganization">
          <Link :href="`/organizations/${currentOrganization.id}/settings`" class="flex items-center">
            <SettingOutlined class="mr-2" />
            Настройки организации
          </Link>
        </a-menu-item>
      </a-menu>
    </template>
  </a-dropdown>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'
import {
  DownOutlined,
  TeamOutlined,
  UserOutlined,
  SettingOutlined
} from '@ant-design/icons-vue'

const { currentOrganization, currentUser } = useOrganizationContext()
const { isOrgAdmin, canViewOrganizationSettings } = useUserPermissions()

// Показывать выпадающее меню только для админов организации (не SuperUser)
const showAdminDropdown = computed(() => {
  return currentUser.value && !currentUser.value.is_super_user && isOrgAdmin.value
})

// Права на управление организацией
const canManageOrganization = computed(() => {
  return canViewOrganizationSettings.value
})
</script>