-- ============================================================
-- No-Code App Builder Platform — Full Database Schema
-- Engine: MySQL 8.0+
-- Strategy: Hybrid EAV (record headers + record_values EAV rows)
--            + JSON snapshot column for query performance
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- ============================================================
-- USERS & AUTHENTICATION
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(120)     NOT NULL,
    `email`       VARCHAR(180)     NOT NULL,
    `password`    VARCHAR(255)     NOT NULL,  -- bcrypt hash
    `is_admin`    TINYINT(1)       NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- APPS — Top-level application definitions
-- ============================================================

CREATE TABLE IF NOT EXISTS `apps` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(120)     NOT NULL,
    `slug`        VARCHAR(120)     NOT NULL,   -- URL-safe identifier
    `description` TEXT             NULL,
    `icon`        VARCHAR(80)      NOT NULL DEFAULT 'bi-grid',  -- Bootstrap Icons class
    `color`       VARCHAR(20)      NOT NULL DEFAULT '#6366f1',  -- Brand color
    `owner_id`    INT UNSIGNED     NOT NULL,
    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
    `settings`    JSON             NULL,       -- App-level config blob
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_apps_slug` (`slug`),
    CONSTRAINT `fk_apps_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MODULES — Sections within an app (e.g., Products, Employees)
-- ============================================================

CREATE TABLE IF NOT EXISTS `modules` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `app_id`        INT UNSIGNED   NOT NULL,
    `name`          VARCHAR(120)   NOT NULL,
    `slug`          VARCHAR(120)   NOT NULL,  -- URL-safe, unique within app
    `description`   TEXT           NULL,
    `icon`          VARCHAR(80)    NOT NULL DEFAULT 'bi-table',
    `sort_order`    INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `settings`      JSON           NULL,      -- Module-level config (pagination, etc.)
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_modules_app_slug` (`app_id`, `slug`),
    CONSTRAINT `fk_modules_app` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIELDS — Field definitions per module
-- Stores type, validation rules, display config, and defaults
-- ============================================================

CREATE TABLE IF NOT EXISTS `fields` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `module_id`      INT UNSIGNED  NOT NULL,
    `name`           VARCHAR(80)   NOT NULL,  -- Display label
    `slug`           VARCHAR(80)   NOT NULL,  -- Machine key (snake_case)
    `field_type`     VARCHAR(40)   NOT NULL,  -- text|number|date|email|dropdown|file|textarea|checkbox
    `is_required`    TINYINT(1)    NOT NULL DEFAULT 0,
    `is_unique`      TINYINT(1)    NOT NULL DEFAULT 0,
    `is_searchable`  TINYINT(1)    NOT NULL DEFAULT 1,
    `show_in_list`   TINYINT(1)    NOT NULL DEFAULT 1,  -- Show as column in list view
    `default_value`  TEXT          NULL,
    `placeholder`    VARCHAR(255)  NULL,
    `help_text`      VARCHAR(255)  NULL,
    -- Validation rules stored as JSON: {"min":0,"max":100,"pattern":"..."}
    `validation`     JSON          NULL,
    -- Field-type-specific options: dropdown choices, file accept types, etc.
    `options`        JSON          NULL,
    `sort_order`     INT           NOT NULL DEFAULT 0,
    `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_fields_module_slug` (`module_id`, `slug`),
    CONSTRAINT `fk_fields_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RECORDS — One row per logical record (header)
-- ============================================================

CREATE TABLE IF NOT EXISTS `records` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `app_id`      INT UNSIGNED     NOT NULL,
    `module_id`   INT UNSIGNED     NOT NULL,
    `created_by`  INT UNSIGNED     NOT NULL,
    `updated_by`  INT UNSIGNED     NULL,
    -- JSON snapshot: {"field_slug": "value", ...}
    -- Maintained as a denormalized copy for fast list queries
    `data`        JSON             NULL,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_records_module` (`module_id`),
    KEY `idx_records_app` (`app_id`),
    CONSTRAINT `fk_records_app`    FOREIGN KEY (`app_id`)    REFERENCES `apps`    (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_records_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_records_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RECORD_VALUES — EAV rows for individual field values
-- Used for fine-grained querying; JSON snapshot above for speed
-- ============================================================

CREATE TABLE IF NOT EXISTS `record_values` (
    `id`         BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `record_id`  INT UNSIGNED      NOT NULL,
    `field_id`   INT UNSIGNED      NOT NULL,
    `value`      MEDIUMTEXT        NULL,      -- All stored as text; FieldEngine casts on read
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rv_record_field` (`record_id`, `field_id`),
    KEY `idx_rv_field_value` (`field_id`, `value`(100)),  -- For filtered queries
    CONSTRAINT `fk_rv_record` FOREIGN KEY (`record_id`) REFERENCES `records`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rv_field`  FOREIGN KEY (`field_id`)  REFERENCES `fields`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RBAC: ROLES
-- ============================================================

CREATE TABLE IF NOT EXISTS `roles` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `app_id`      INT UNSIGNED     NOT NULL,
    `name`        VARCHAR(80)      NOT NULL,
    `description` VARCHAR(255)     NULL,
    `is_system`   TINYINT(1)       NOT NULL DEFAULT 0,  -- System roles cannot be deleted
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_app_name` (`app_id`, `name`),
    CONSTRAINT `fk_roles_app` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RBAC: USER ↔ ROLE ASSIGNMENTS
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id`    INT UNSIGNED     NOT NULL,
    `role_id`    INT UNSIGNED     NOT NULL,
    `app_id`     INT UNSIGNED     NOT NULL,
    `assigned_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `role_id`, `app_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_app`  FOREIGN KEY (`app_id`)  REFERENCES `apps`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RBAC: PERMISSIONS — Module-level CRUD grants per role
-- ============================================================

CREATE TABLE IF NOT EXISTS `permissions` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `role_id`     INT UNSIGNED     NOT NULL,
    `module_id`   INT UNSIGNED     NOT NULL,
    `can_view`    TINYINT(1)       NOT NULL DEFAULT 0,
    `can_create`  TINYINT(1)       NOT NULL DEFAULT 0,
    `can_edit`    TINYINT(1)       NOT NULL DEFAULT 0,
    `can_delete`  TINYINT(1)       NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_perms_role_module` (`role_id`, `module_id`),
    CONSTRAINT `fk_perms_role`   FOREIGN KEY (`role_id`)   REFERENCES `roles`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_perms_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WORKFLOWS — If/then automation rules
-- ============================================================

CREATE TABLE IF NOT EXISTS `workflows` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `module_id`    INT UNSIGNED    NOT NULL,
    `name`         VARCHAR(120)    NOT NULL,
    `trigger_on`   ENUM('create','update','delete','create_update') NOT NULL DEFAULT 'create_update',
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    -- Conditions JSON: [{"field_id":1,"operator":"equals","value":"Low Stock"}]
    `conditions`   JSON            NULL,
    `condition_logic` ENUM('AND','OR') NOT NULL DEFAULT 'AND',
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_wf_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WORKFLOW_ACTIONS — Actions triggered when workflow conditions match
-- ============================================================

CREATE TABLE IF NOT EXISTS `workflow_actions` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `workflow_id`  INT UNSIGNED    NOT NULL,
    `action_type`  ENUM('send_email','update_field','notification') NOT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    -- Config JSON per action type:
    --  send_email:    {"to":"{{email_field}}","subject":"...","body":"..."}
    --  update_field:  {"field_id":5,"value":"Processed"}
    --  notification:  {"message":"Record {{id}} updated"}
    `config`       JSON            NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_wa_workflow` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DASHBOARD_WIDGETS — Saved widget configurations per user/app
-- ============================================================

CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `app_id`       INT UNSIGNED    NOT NULL,
    `user_id`      INT UNSIGNED    NOT NULL,
    `title`        VARCHAR(120)    NOT NULL,
    `widget_type`  ENUM('count','sum','average','bar_chart','pie_chart') NOT NULL,
    `module_id`    INT UNSIGNED    NOT NULL,
    `field_id`     INT UNSIGNED    NULL,        -- NULL for count widgets
    -- Filter JSON: [{"field_id":3,"operator":"lt","value":"10"}]
    `filters`      JSON            NULL,
    `chart_color`  VARCHAR(20)     NOT NULL DEFAULT '#6366f1',
    `position_x`   TINYINT        NOT NULL DEFAULT 0,
    `position_y`   TINYINT        NOT NULL DEFAULT 0,
    `width`        TINYINT        NOT NULL DEFAULT 4,  -- Bootstrap col width (1-12)
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_dw_app`    FOREIGN KEY (`app_id`)    REFERENCES `apps`    (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_dw_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_dw_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AUDIT_LOG — Records every create/update/delete action
-- (Used by AuditLogPlugin)
-- ============================================================

CREATE TABLE IF NOT EXISTS `audit_log` (
    `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NULL,
    `app_id`      INT UNSIGNED     NULL,
    `module_id`   INT UNSIGNED     NULL,
    `record_id`   INT UNSIGNED     NULL,
    `action`      VARCHAR(20)      NOT NULL,   -- create|update|delete|login
    `old_data`    JSON             NULL,
    `new_data`    JSON             NULL,
    `ip_address`  VARCHAR(45)      NULL,
    `user_agent`  VARCHAR(255)     NULL,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_al_user`   (`user_id`),
    KEY `idx_al_record` (`record_id`),
    KEY `idx_al_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS — In-app notification store
-- ============================================================

CREATE TABLE IF NOT EXISTS `notifications` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `app_id`      INT UNSIGNED     NULL,
    `title`       VARCHAR(120)     NOT NULL,
    `message`     TEXT             NULL,
    `is_read`     TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_user` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
