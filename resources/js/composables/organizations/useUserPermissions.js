import { computed } from 'vue'
import { useOrganizationContext } from './useOrganizationContext'

/**
 * Composable для проверки пользовательских прав
 * Permission layer - централизованная проверка доступа и прав
 */
export function useUserPermissions() {
  const { currentUser, currentUserRole, isSuperUser, hasRole } = useOrganizationContext()

  // Базовые проверки ролей
  const isOrgAdmin = computed(() => hasRole('org_admin'))
  const isProjectManager = computed(() => hasRole('project_manager'))
  const isMember = computed(() => hasRole('member'))

  // Права на управление организацией
  const canCreateOrganization = computed(() => !!currentUser.value) // Все авторизованные могут создавать
  const canUpdateOrganization = computed(() => hasRole(['org_admin', 'super_user']))
  const canDeleteOrganization = computed(() => isSuperUser.value)
  const canViewOrganizationSettings = computed(() => hasRole(['org_admin', 'super_user']))

  // Права на управление участниками
  const canViewMembers = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))
  const canInviteMembers = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canManageMembers = computed(() => hasRole(['org_admin', 'super_user']))
  const canRemoveMembers = computed(() => hasRole(['org_admin', 'super_user']))
  const canChangeRoles = computed(() => hasRole(['org_admin', 'super_user']))

  // Права на проекты
  const canViewProjects = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))
  const canCreateProjects = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canUpdateProjects = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canDeleteProjects = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))

  // Права на задачи
  const canViewTasks = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))
  const canCreateTasks = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canUpdateTasks = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canDeleteTasks = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canAssignTasks = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))

  // Права на время и активности
  const canLogTime = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))
  const canViewAllTimeLogs = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))
  const canEditOthersTimeLogs = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))

  // Права на статусы
  const canViewStatuses = computed(() => hasRole(['member', 'project_manager', 'org_admin', 'super_user']))
  const canManageStatuses = computed(() => hasRole(['project_manager', 'org_admin', 'super_user']))

  /**
   * Проверить может ли пользователь редактировать конкретный ресурс
   * @param {Object} resource - ресурс (задача, проект, etc.)
   * @param {string} action - действие (edit, delete, etc.)
   */
  const canEditResource = (resource, action = 'edit') => {
    if (!resource || !currentUser.value) return false
    if (isSuperUser.value) return true

    // Проверяем является ли пользователь создателем ресурса
    if (resource.created_by === currentUser.value.id) {
      return true
    }

    // Проверяем роли в зависимости от действия
    switch (action) {
      case 'edit':
      case 'update':
        return hasRole(['project_manager', 'org_admin'])
      case 'delete':
        return hasRole(['org_admin'])
      default:
        return false
    }
  }

  /**
   * Проверить может ли пользователь выполнить действие над другим пользователем
   * @param {Object} targetUser - целевой пользователь
   * @param {string} action - действие (invite, remove, change_role)
   */
  const canActOnUser = (targetUser, action) => {
    if (!targetUser || !currentUser.value) return false
    if (isSuperUser.value) return true

    // Нельзя выполнять действия над собой (кроме некоторых случаев)
    if (targetUser.id === currentUser.value.id) {
      return ['update_profile', 'change_password'].includes(action)
    }

    // Нельзя выполнять действия над супер пользователями (если сам не супер)
    if (targetUser.is_super_user && !isSuperUser.value) {
      return false
    }

    switch (action) {
      case 'invite':
        return hasRole(['project_manager', 'org_admin'])
      case 'remove':
      case 'change_role':
        return hasRole(['org_admin'])
      case 'view':
        return hasRole(['member', 'project_manager', 'org_admin'])
      default:
        return false
    }
  }

  /**
   * Получить список доступных ролей для назначения
   */
  const getAssignableRoles = computed(() => {
    if (isSuperUser.value) {
      return [
        { value: 'member', label: 'Участник' },
        { value: 'project_manager', label: 'Менеджер проектов' },
        { value: 'org_admin', label: 'Администратор организации' }
      ]
    }

    if (isOrgAdmin.value) {
      return [
        { value: 'member', label: 'Участник' },
        { value: 'project_manager', label: 'Менеджер проектов' },
        { value: 'org_admin', label: 'Администратор организации' }
      ]
    }

    if (isProjectManager.value) {
      return [
        { value: 'member', label: 'Участник' }
      ]
    }

    return []
  })

  /**
   * Получить название роли для отображения
   */
  const getRoleLabel = (role) => {
    if (!role) return 'Загрузка...'
    
    const roleLabels = {
      'super_user': 'Супер администратор',
      'org_admin': 'Администратор организации',
      'project_manager': 'Менеджер проектов',
      'member': 'Участник'
    }
    return roleLabels[role] || role
  }

  /**
   * Проверить уровень доступа к функционалу
   */
  const hasFeatureAccess = (feature) => {
    const featurePermissions = {
      'organizations_management': ['super_user'],
      'organization_settings': ['org_admin', 'super_user'],
      'members_management': ['org_admin', 'super_user'],
      'projects_management': ['project_manager', 'org_admin', 'super_user'],
      'tasks_management': ['project_manager', 'org_admin', 'super_user'],
      'time_tracking': ['member', 'project_manager', 'org_admin', 'super_user'],
      'reports': ['project_manager', 'org_admin', 'super_user'],
      'user_invitations': ['project_manager', 'org_admin', 'super_user']
    }

    const requiredRoles = featurePermissions[feature]
    return requiredRoles ? hasRole(requiredRoles) : false
  }

  return {
    // Роли и состояние
    currentUser,
    currentUserRole,
    isSuperUser,
    isOrgAdmin,
    isProjectManager,
    isMember,

    // Права на организации
    canCreateOrganization,
    canUpdateOrganization,
    canDeleteOrganization,
    canViewOrganizationSettings,

    // Права на участников
    canViewMembers,
    canInviteMembers,
    canManageMembers,
    canRemoveMembers,
    canChangeRoles,

    // Права на проекты
    canViewProjects,
    canCreateProjects,
    canUpdateProjects,
    canDeleteProjects,

    // Права на задачи
    canViewTasks,
    canCreateTasks,
    canUpdateTasks,
    canDeleteTasks,
    canAssignTasks,

    // Права на время
    canLogTime,
    canViewAllTimeLogs,
    canEditOthersTimeLogs,

    // Права на статусы
    canViewStatuses,
    canManageStatuses,

    // Утилиты
    hasRole,
    canEditResource,
    canActOnUser,
    hasFeatureAccess,
    getAssignableRoles,
    getRoleLabel
  }
}