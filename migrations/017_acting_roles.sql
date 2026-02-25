-- ═══════════════════════════════════════════════════════════════
-- Migration 017: Acting Role Assignments
-- ═══════════════════════════════════════════════════════════════
-- Allows users to temporarily act in another role (e.g. HOD
-- acting as Deputy GC while on leave). Admins assign acting
-- roles with optional date ranges. Users can switch between
-- their primary role and any active acting role.
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `acting_roles` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL,
    `acting_role_id` INT NOT NULL COMMENT 'The role this user can act in',
    `assigned_by`   INT NOT NULL COMMENT 'Admin who created the assignment',
    `reason`        VARCHAR(255) DEFAULT NULL COMMENT 'e.g. "Leave cover for J. Smith"',
    `starts_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ends_at`       DATETIME DEFAULT NULL COMMENT 'NULL = indefinite until manually revoked',
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)        REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`acting_role_id`) REFERENCES `roles`(`id`)      ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`)    REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_user_acting_role` (`user_id`, `acting_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log for every role switch
CREATE TABLE IF NOT EXISTS `acting_role_log` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL,
    `switched_from_role_id` INT NOT NULL,
    `switched_to_role_id`   INT NOT NULL,
    `is_acting`     TINYINT(1) NOT NULL COMMENT '1=switched to acting, 0=reverted to primary',
    `ip_address`    VARCHAR(45) DEFAULT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
