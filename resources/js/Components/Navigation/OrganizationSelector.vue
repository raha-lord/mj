<template>
  <!-- Не показываем селектор организаций для SuperUser -->
  <a-dropdown
    v-if="!isSuperUser"
    v-model:open="dropdownOpen"
    :trigger="['click']"
    placement="bottomRight"
  >
    <a-button type="text" class="ant-dropdown-link organization-selector">
      <template v-if="currentOrganization">
        <TeamOutlined class="mr-2" />
        {{ currentOrganization.name }}
      </template>
      <template v-else>
        <TeamOutlined class="mr-2" />
        <span class="text-gray-400">Выберите организацию</span>
      </template>
      <DownOutlined class="ml-2" />
      <a-badge 
        v-if="pendingInvitationsCount > 0" 
        :count="pendingInvitationsCount" 
        :offset="[10, -5]"
        class="ml-1"
      />
    </a-button>

    <template #overlay>
      <a-menu class="organization-dropdown-menu" style="min-width: 250px;">
        <!-- Текущая организация -->
        <template v-if="currentOrganization">
          <a-menu-item disabled class="current-org-header">
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-500 uppercase">Текущая организация</span>
              <CheckOutlined class="text-green-500" />
            </div>
          </a-menu-item>
          <a-menu-item disabled class="current-org-item">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <TeamOutlined class="mr-2 text-primary" />
                <div>
                  <div class="font-medium">{{ currentOrganization.name }}</div>
                  <div class="text-xs text-gray-500">{{ getRoleLabel(currentUserRole) }}</div>
                </div>
              </div>
              <a-button
                v-if="currentUserRole === 'org_admin'"
                type="text"
                size="small"
                @click.stop="handleOrganizationSettings"
                class="settings-btn"
              >
                <SettingOutlined class="text-gray-500 hover:text-blue-500" />
              </a-button>
            </div>
          </a-menu-item>
          <a-menu-divider />
        </template>

        <!-- Список доступных организаций -->
        <a-menu-item-group title="Доступные организации">
          <template v-if="isLoadingOrganizations">
            <a-menu-item disabled>
              <div class="flex items-center justify-center py-2">
                <LoadingOutlined class="mr-2" />
                Загрузка...
              </div>
            </a-menu-item>
          </template>

          <template v-else-if="availableOrganizations.length > 0">
            <a-menu-item
              v-for="org in availableOrganizations"
              :key="org.id"
              @click="handleSwitchOrganization(org.id)"
              :disabled="org.id === currentOrganization?.id"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <TeamOutlined class="mr-2" />
                  <div>
                    <div class="font-medium">{{ org.name }}</div>
                    <div class="text-xs text-gray-500">{{ getRoleLabel(org.user_role) }}</div>
                  </div>
                </div>
                <a-button
                  v-if="org.user_role === 'org_admin'"
                  type="text"
                  size="small"
                  @click.stop="(event) => { event.preventDefault(); handleOrganizationSettings(org.id); }"
                  class="settings-btn"
                >
                  <SettingOutlined class="text-gray-500 hover:text-blue-500" />
                </a-button>
              </div>
            </a-menu-item>
          </template>

          <template v-else>
            <a-menu-item disabled>
              <div class="text-center text-gray-500 py-2">
                Нет доступных организаций
              </div>
            </a-menu-item>
          </template>
        </a-menu-item-group>

        <!-- Приглашения в организации -->
        <template v-if="invitations.length > 0">
          <a-menu-divider />
          <a-menu-item-group title="Приглашения">
            <a-menu-item
              v-for="invitation in invitations"
              :key="`invitation-${invitation.id}`"
              class="invitation-item"
            >
              <div class="flex flex-col space-y-2">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <TeamOutlined class="mr-2 text-orange-500" />
                    <div>
                      <div class="font-medium">{{ invitation.organization.name }}</div>
                      <div class="text-xs text-gray-500">
                        Роль: {{ getRoleLabel(invitation.role) }}
                      </div>
                      <div class="text-xs text-gray-400">
                        От: {{ invitation.inviter.name }}
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="invitation.message" class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                  {{ invitation.message }}
                </div>
                <div class="flex space-x-2">
                  <a-button 
                    type="primary" 
                    size="small"
                    @click.stop="handleAcceptInvitation(invitation.id)"
                    class="flex-1"
                  >
                    Принять
                  </a-button>
                  <a-button 
                    size="small"
                    @click.stop="handleDeclineInvitation(invitation.id)"
                    class="flex-1"
                  >
                    Отклонить
                  </a-button>
                </div>
              </div>
            </a-menu-item>
          </a-menu-item-group>
        </template>

        <a-menu-divider />

        <!-- Действия -->

        <a-menu-item
          v-if="isSuperUser"
          @click="handleManageOrganizations"
        >
          <SettingOutlined class="mr-2" />
          Управление организациями
        </a-menu-item>

        <!-- Настройки текущей организации -->
        <template v-if="currentOrganization && canManageOrganization">
          <a-menu-divider />
          <a-menu-item @click="handleOrganizationSettings">
            <SettingOutlined class="mr-2" />
            Настройки организации
          </a-menu-item>
        </template>
      </a-menu>
    </template>
  </a-dropdown>

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  TeamOutlined,
  DownOutlined,
  CheckOutlined,
  LoadingOutlined,
  SettingOutlined
} from '@ant-design/icons-vue'
import { useOrganizations } from '../../composables/organizations/useOrganizations'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'
import { useInvitations } from '../../composables/organizations/useInvitations'
import { message } from 'ant-design-vue'

// Composables
const {
  organizations,
  loading: isLoadingOrganizations,
  fetchOrganizations
} = useOrganizations()

const {
  currentOrganization,
  currentUserRole,
  switchOrganization,
  initializeContext
} = useOrganizationContext()

const {
  canManageOrganization,
  isSuperUser,
  getRoleLabel
} = useUserPermissions()

const {
  invitations,
  pendingInvitationsCount,
  fetchInvitations,
  acceptInvitation,
  declineInvitation
} = useInvitations()

// Состояние компонента
const dropdownOpen = ref(false)

// Вычисляемые свойства
const availableOrganizations = computed(() => {
  return organizations.value.filter(org => 
    !currentOrganization.value || org.id !== currentOrganization.value.id
  )
})

// Методы
const handleSwitchOrganization = async (organizationId) => {
  try {
    await switchOrganization(organizationId)
    message.success('Организация успешно переключена')
    dropdownOpen.value = false
    
    // Перезагружаем страницу для обновления контекста
    router.reload()
  } catch (error) {
    message.error(error.message || 'Ошибка переключения организации')
  }
}


const handleManageOrganizations = () => {
  router.visit('/organizations')
  dropdownOpen.value = false
}

const handleOrganizationSettings = (organizationId = null) => {
  // Если organizationId это event object, игнорируем его
  let orgId = null
  if (organizationId && typeof organizationId === 'number') {
    orgId = organizationId
  } else {
    orgId = currentOrganization.value?.id
  }
  
  if (orgId) {
    router.visit(`/organizations/${orgId}/settings`)
    dropdownOpen.value = false
  }
}

const handleAcceptInvitation = async (invitationId) => {
  try {
    const result = await acceptInvitation(invitationId)
    dropdownOpen.value = false
    
    // Обновляем контекст и перезагружаем страницу для переключения на новую организацию
    await initializeContext()
    router.reload()
  } catch (error) {
    // Ошибка уже обработана в composable
  }
}

const handleDeclineInvitation = async (invitationId) => {
  try {
    await declineInvitation(invitationId)
    // dropdownOpen остается открытым, чтобы пользователь мог видеть другие приглашения
  } catch (error) {
    // Ошибка уже обработана в composable
  }
}

// Загрузка данных при открытии dropdown
watch(dropdownOpen, (isOpen) => {
  if (isOpen) {
    if (organizations.value.length === 0) {
      fetchOrganizations()
    }
    // Всегда загружаем актуальные приглашения
    fetchInvitations()
  }
})

// Инициализация
onMounted(async () => {
  if (!isSuperUser.value) {
    await initializeContext()
    await fetchOrganizations()
    await fetchInvitations()
  }
})
</script>

<style scoped>
.organization-selector {
  @apply flex items-center;
}

.organization-dropdown-menu .current-org-header {
  @apply px-3 py-1;
}

.organization-dropdown-menu .current-org-item {
  @apply px-3 py-2 bg-blue-50;
}


.organization-dropdown-menu .ant-menu-item:hover {
  @apply bg-gray-50;
}

.organization-dropdown-menu .ant-menu-item.ant-menu-item-disabled {
  @apply opacity-100;
}

.settings-btn {
  @apply opacity-70 hover:opacity-100 transition-opacity;
}

.settings-btn:hover .anticon {
  @apply text-blue-500;
}

.invitation-item {
  @apply px-3 py-2;
  min-width: 280px;
}

.invitation-item:hover {
  @apply bg-orange-50;
}
</style>