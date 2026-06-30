-- Migration 000004: OAuth 2.1 support tables
--
-- Adds support for OAuth 2.1 with PKCE (RFC 9728 / RFC 7591).
-- Access and refresh tokens are STATELESS (HMAC-signed, never stored).
-- The HMAC signing key is user_api_tokens.token_hash — revoking a master
-- API token immediately invalidates every OAuth token derived from it.
--
-- Requires: 000003_user_api_tokens.sql (api_token_id references that table)

SET NAMES utf8mb4;
SET default_storage_engine = InnoDB;

-- OAuth clients registered via Dynamic Client Registration (RFC 7591).
-- Public clients only (token_endpoint_auth_method = "none").
CREATE TABLE IF NOT EXISTS `oauth_clients` (
    `id`            VARCHAR(40)   NOT NULL                       COMMENT 'Random hex client_id',
    `client_name`   VARCHAR(200)  NOT NULL DEFAULT '',
    `redirect_uris` TEXT          NOT NULL                       COMMENT 'JSON array of allowed redirect URIs',
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Short-lived authorization codes (deleted immediately on use, 10 min max TTL).
-- Lazy GC removes stragglers via DELETE WHERE expires_at < NOW().
CREATE TABLE IF NOT EXISTS `oauth_auth_codes` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `code_hash`      CHAR(64)      NOT NULL                      COMMENT 'SHA-256 of the code (never stored in clear)',
    `client_id`      VARCHAR(40)   NOT NULL,
    `api_token_id`   INT UNSIGNED  NOT NULL                      COMMENT 'user_api_tokens.id — HMAC anchor for derived tokens',
    `redirect_uri`   VARCHAR(500)  NOT NULL,
    `code_challenge` VARCHAR(128)  NOT NULL                      COMMENT 'PKCE S256 challenge',
    `expires_at`     DATETIME      NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_oauth_code_hash` (`code_hash`),
    KEY `idx_oauth_code_expires`    (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
