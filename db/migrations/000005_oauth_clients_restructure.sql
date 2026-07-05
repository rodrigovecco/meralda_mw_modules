-- Migration 000005: Restructure oauth_clients — add auto-increment id, move OAuth
-- identifier to a dedicated client_id column.
--
-- Migration 000004 created oauth_clients with `id` VARCHAR(40) as the PK.
-- The Meralda convention is that `id` is always INT AUTO_INCREMENT. This
-- migration drops and recreates the table correctly. It is safe to do so
-- because DCR was non-functional until migration 000004's companion code fix
-- (validateAllowedAsRoot), so the table is guaranteed to be empty in production.

SET NAMES utf8mb4;
SET default_storage_engine = InnoDB;

DROP TABLE IF EXISTS `oauth_clients`;

CREATE TABLE `oauth_clients` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `client_id`     VARCHAR(40)   NOT NULL                       COMMENT 'Random hex OAuth client identifier (RFC 7591)',
    `client_name`   VARCHAR(200)  NOT NULL DEFAULT '',
    `redirect_uris` TEXT          NOT NULL                       COMMENT 'JSON array of allowed redirect URIs',
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_oauth_clients_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
