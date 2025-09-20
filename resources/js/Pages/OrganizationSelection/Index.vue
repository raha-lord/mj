<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <TeamOutlined class="text-6xl text-blue-500" />
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Выберите организацию
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        У вас есть приглашения в организации. Пожалуйста, примите одно из них чтобы продолжить.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <!-- Загрузка приглашений -->
        <div v-if="isLoading" class="space-y-4">
          <a-card v-for="i in 3" :key="i" :loading="true" />
        </div>

        <!-- Список приглашений -->
        <div v-else-if="invitations.length > 0" class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Ваши приглашения:
          </h3>
          
          <div
            v-for="invitation in invitations"
            :key="invitation.id"
            class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
          >
            <div class="flex items-start space-x-3">
              <TeamOutlined class="text-2xl text-blue-500 mt-1" />
              <div class="flex-1 min-w-0">
                <h4 class="text-lg font-medium text-gray-900">
                  {{ invitation.organization.name }}
                </h4>
                <p class="text-sm text-gray-500 mt-1">
                  {{ invitation.organization.description }}
                </p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                  <span>Роль: {{ getRoleLabel(invitation.role) }}</span>
                  <span>От: {{ invitation.inviter.name }}</span>
                </div>
                <div v-if="invitation.message" class="mt-3 p-3 bg-gray-50 rounded-md">
                  <p class="text-sm text-gray-700">{{ invitation.message }}</p>
                </div>
                <div class="mt-4 flex space-x-3">
                  <a-button 
                    type="primary"
                    @click="handleAcceptInvitation(invitation.id)"
                    :loading="acceptingInvitationId === invitation.id"
                    class="flex-1"
                  >
                    Принять приглашение
                  </a-button>
                  <a-button 
                    @click="handleDeclineInvitation(invitation.id)"
                    :loading="decliningInvitationId === invitation.id"
                    class="flex-1"
                  >
                    Отклонить
                  </a-button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Нет приглашений -->
        <div v-else class="text-center">
          <TeamOutlined class="text-4xl text-gray-300 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            Нет активных приглашений
          </h3>
          <p class="text-gray-500 mb-6">
            У вас нет доступных приглашений в организации. 
            Обратитесь к администратору для получения доступа.
          </p>
          <a-button type="primary" @click="refreshInvitations">
            Обновить
          </a-button>
        </div>

        <!-- Кнопка обновления -->
        <div v-if="invitations.length > 0" class="mt-6 text-center">
          <a-button @click="refreshInvitations" :loading="isLoading">
            Обновить список приглашений
          </a-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { TeamOutlined } from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import { router } from '@inertiajs/vue3'
import { useInvitations } from '../../composables/organizations/useInvitations'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'

// Composables
const { 
  invitations, 
  loading: isLoading, 
  fetchInvitations, 
  acceptInvitation, 
  declineInvitation 
} = useInvitations()

const { getRoleLabel } = useUserPermissions()

// Состояние
const acceptingInvitationId = ref(null)
const decliningInvitationId = ref(null)

// Методы
const handleAcceptInvitation = async (invitationId) => {
  acceptingInvitationId.value = invitationId
  try {
    await acceptInvitation(invitationId)
    message.success('Приглашение принято! Перенаправляем...')
    
    // Небольшая задержка для показа сообщения
    setTimeout(() => {
      router.visit('/dashboard')
    }, 1500)
  } catch (error) {
    // Ошибка уже обработана в composable
  } finally {
    acceptingInvitationId.value = null
  }
}

const handleDeclineInvitation = async (invitationId) => {
  decliningInvitationId.value = invitationId
  try {
    await declineInvitation(invitationId)
    // Если это было последнее приглашение, обновляем список
    if (invitations.value.length === 1) {
      await refreshInvitations()
    }
  } catch (error) {
    // Ошибка уже обработана в composable
  } finally {
    decliningInvitationId.value = null
  }
}

const refreshInvitations = async () => {
  await fetchInvitations()
}

// Инициализация
onMounted(async () => {
  await fetchInvitations()
})
</script>

<style scoped>
/* Дополнительные стили если нужны */
</style>