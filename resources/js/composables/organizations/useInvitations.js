import { ref, computed } from 'vue'
import { message } from 'ant-design-vue'

const invitations = ref([])
const loading = ref(false)

export function useInvitations() {
  /**
   * Загрузить приглашения для текущего пользователя
   */
  const fetchInvitations = async () => {
    loading.value = true
    try {
      const response = await fetch('/api/invitations', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      invitations.value = data.data || []
    } catch (error) {
      console.error('Error fetching invitations:', error)
      message.error('Ошибка загрузки приглашений')
    } finally {
      loading.value = false
    }
  }

  /**
   * Принять приглашение
   */
  const acceptInvitation = async (invitationId) => {
    try {
      const response = await fetch(`/api/invitations/${invitationId}/accept`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        let errorMessage = `HTTP ${response.status}`
        try {
          const errorData = await response.json()
          errorMessage = errorData.message || errorMessage
        } catch (parseError) {
          // Если не можем распарсить JSON, используем текст ответа
          const textResponse = await response.text()
          console.error('Failed to parse error response as JSON:', parseError)
          console.error('Response text:', textResponse)
          errorMessage = textResponse || errorMessage
        }
        throw new Error(errorMessage)
      }

      const data = await response.json()
      
      // Удаляем приглашение из списка
      invitations.value = invitations.value.filter(inv => inv.id !== invitationId)
      
      message.success(data.message || 'Приглашение принято')
      
      return data
    } catch (error) {
      console.error('Error accepting invitation:', error)
      message.error(error.message || 'Ошибка принятия приглашения')
      throw error
    }
  }

  /**
   * Отклонить приглашение
   */
  const declineInvitation = async (invitationId) => {
    try {
      const response = await fetch(`/api/invitations/${invitationId}/decline`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        let errorMessage = `HTTP ${response.status}`
        try {
          const errorData = await response.json()
          errorMessage = errorData.message || errorMessage
        } catch (parseError) {
          // Если не можем распарсить JSON, используем текст ответа
          const textResponse = await response.text()
          console.error('Failed to parse error response as JSON:', parseError)
          console.error('Response text:', textResponse)
          errorMessage = textResponse || errorMessage
        }
        throw new Error(errorMessage)
      }

      const data = await response.json()
      
      // Удаляем приглашение из списка
      invitations.value = invitations.value.filter(inv => inv.id !== invitationId)
      
      message.success(data.message || 'Приглашение отклонено')
      
      return data
    } catch (error) {
      console.error('Error declining invitation:', error)
      message.error(error.message || 'Ошибка отклонения приглашения')
      throw error
    }
  }

  /**
   * Количество ожидающих приглашений
   */
  const pendingInvitationsCount = computed(() => invitations.value.length)

  return {
    invitations: computed(() => invitations.value),
    loading: computed(() => loading.value),
    pendingInvitationsCount,
    fetchInvitations,
    acceptInvitation,
    declineInvitation
  }
}