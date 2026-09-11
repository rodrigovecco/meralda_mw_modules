<?php
/**
 * Core migration 000004: OAuth 2.1 support tables.
 *
 * Adds support for OAuth 2.1 with PKCE (RFC 9728 / RFC 7591).
 * Access and refresh tokens are STATELESS (HMAC-signed, never stored).
 * The HMAC signing key is user_api_tokens.token_hash — revoking a master
 * API token immediately invalidates every OAuth token derived from it.
 *
 * Requires: m000003userapitokens (api_token_id references that table).
 *
 * Note: oauth_clients is later restructured by m000005oauthclientsrestructure.
 */
class mwmod_mw_dbcore_m000004oauthtables extends mwmod_mw_db_migrations_itemabs {

	function get_description() {
		return "OAuth 2.1 support tables";
	}

	function check() {
		return !$this->table_exists("oauth_clients")
			|| !$this->table_exists("oauth_auth_codes");
	}

	function apply() {
		return $this->run_sql("
			CREATE TABLE IF NOT EXISTS `oauth_clients` (
			    `id`            VARCHAR(40)   NOT NULL                       COMMENT 'Random hex client_id',
			    `client_name`   VARCHAR(200)  NOT NULL DEFAULT '',
			    `redirect_uris` TEXT          NOT NULL                       COMMENT 'JSON array of allowed redirect URIs',
			    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
		");
	}

}
