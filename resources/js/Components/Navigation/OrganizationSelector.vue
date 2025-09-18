<template>
  <a-dropdown
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
            <div class="flex items-center">
              <TeamOutlined class="mr-2 text-primary" />
              <div>
                <div class="font-medium">{{ currentOrganization.name }}</div>
                <div class="text-xs text-gray-500">{{ getRoleLabel(currentUserRole) }}</div>
              </div>
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
                <div class="text-xs text-gray-400">
                  {{ org.users_count }} участников
                </div>
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

        <a-menu-divider />

        <!-- Действия -->
        <a-menu-item
          v-if="canCreateOrganization"
          @click="handleCreateOrganization"
          class="create-org-item"
        >
          <PlusOutlined class="mr-2" />
          Создать организацию
        </a-menu-item>

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

  <!-- Модал создания организации -->
  <a-modal
    v-model:open="createModalOpen"
    title="Создать новую организацию"
    :confirm-loading="isCreating"
    @ok="handleCreateSubmit"
    @cancel="handleCreateCancel"
  >
    <a-form
      :model="createForm"
      layout="vertical"
      @finish="handleCreateSubmit"
    >
      <a-form-item
        label="Название организации"
        name="name"
        :rules="[
          { required: true, message: 'Введите название организации' },
          { min: 2, max: 255, message: 'Название должно быть от 2 до 255 символов' }
        ]"
      >
        <a-input
          v-model:value="createForm.name"
          placeholder="Введите название организации"
          :maxlength="255"
        />
      </a-form-item>

      <a-form-item
        label="Описание (необязательно)"
        name="description"
      >
        <a-textarea
          v-model:value="createForm.description"
          placeholder="Описание организации"
          :rows="3"
          :maxlength="1000"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  TeamOutlined,
  DownOutlined,
  CheckOutlined,
  LoadingOutlined,
  PlusOutlined,
  SettingOutlined
} from '@ant-design/icons-vue'
import { useOrganizations } from '../../composables/organizations/useOrganizations'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'
import { message } from 'ant-design-vue'

// Composables
const {
  organizations,
  loading: isLoadingOrganizations,
  createOrganization,
  fetchOrganizations
} = useOrganizations()

const {
  currentOrganization,
  currentUserRole,
  switchOrganization,
  initializeContext
} = useOrganizationContext()

const {
  canCreateOrganization,
  canManageOrganization,
  isSuperUser,
  getRoleLabel
} = useUserPermissions()

// Состояние компонента
const dropdownOpen = ref(false)
const createModalOpen = ref(false)
const isCreating = ref(false)

// Форма создания организации
const createForm = ref({
  name: '',
  description: ''
})

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

const handleCreateOrganization = () => {
  createModalOpen.value = true
  createForm.value = {
    name: '',
    description: ''
  }
}

const handleCreateSubmit = async () => {
  if (!createForm.value.name.trim()) {
    message.error('Введите название организации')
    return
  }

  isCreating.value = true
  
  try {
    const newOrganization = await createOrganization({
      name: createForm.value.name.trim(),
      description: createForm.value.description.trim() || null
    })

    message.success('Организация успешно создана')
    createModalOpen.value = false
    
    // Переключаемся на новую организацию
    await handleSwitchOrganization(newOrganization.id)
  } catch (error) {
    message.error(error.message || 'Ошибка создания организации')
  } finally {
    isCreating.value = false
  }
}

const handleCreateCancel = () => {
  createModalOpen.value = false
  createForm.value = {
    name: '',
    description: ''
  }
}

const handleManageOrganizations = () => {
  router.visit('/organizations')
  dropdownOpen.value = false
}

const handleOrganizationSettings = () => {
  if (currentOrganization.value) {
    router.visit(`/organizations/${currentOrganization.value.id}/settings`)
    dropdownOpen.value = false
  }
}

// Загрузка данных при открытии dropdown
watch(dropdownOpen, (isOpen) => {
  if (isOpen && organizations.value.length === 0) {
    fetchOrganizations()
  }
})

// Инициализация
onMounted(async () => {
  await initializeContext()
  await fetchOrganizations()
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

.organization-dropdown-menu .create-org-item {
  @apply text-blue-600 font-medium;
}

.organization-dropdown-menu .ant-menu-item:hover {
  @apply bg-gray-50;
}

.organization-dropdown-menu .ant-menu-item.ant-menu-item-disabled {
  @apply opacity-100;
}
</style>