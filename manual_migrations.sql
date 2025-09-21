-- Миграции для системы управления участниками проектов
-- Выполните эти команды в вашей базе данных PostgreSQL

-- 1. Добавляем поля видимости в таблицу projects
ALTER TABLE projects 
ADD COLUMN visibility VARCHAR(20) DEFAULT 'public' CHECK (visibility IN ('public', 'private')),
ADD COLUMN access_description TEXT NULL;

-- 2. Создаем таблицу project_user для связи участников с проектами
CREATE TABLE project_user (
    id BIGSERIAL PRIMARY KEY,
    project_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(20) DEFAULT 'member' CHECK (role IN ('member', 'manager')),
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    deleted_by BIGINT NULL,
    
    CONSTRAINT fk_project_user_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_user_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_user_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_project_user_updated_by FOREIGN KEY (updated_by) REFERENCES users(id),
    CONSTRAINT fk_project_user_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id),
    
    UNIQUE(project_id, user_id)
);

-- 3. Создаем индексы для оптимизации
CREATE INDEX idx_project_user_project_role ON project_user(project_id, role);
CREATE INDEX idx_project_user_deleted_at ON project_user(deleted_at);

-- 4. Добавляем комментарии для документации
COMMENT ON TABLE project_user IS 'Связь участников с проектами';
COMMENT ON COLUMN project_user.role IS 'Роль пользователя в проекте: member, manager';
COMMENT ON COLUMN projects.visibility IS 'Видимость проекта: public, private';
COMMENT ON COLUMN projects.access_description IS 'Описание доступа для приватных проектов';

-- После выполнения этих команд нужно будет раскомментировать код в контроллерах и Vue компонентах