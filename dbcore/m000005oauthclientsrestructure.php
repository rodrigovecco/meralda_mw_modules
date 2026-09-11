<?php
/**
 * Core migration 000005: restructure oauth_clients.
 *
 * Migration 000004 created oauth_clients with `id` VARCHAR(40) as the PK.
 * The Meralda convention is that `id` is always INT AUTO_INCREMENT, so the
 * OAuth identifier is moved to a dedicated `client_id` column.
 *
 * DCR was non-functional before the validateAllowedAsRoot fix, so the table is
 * empty in production — safe to recreate.
 *
 * Requires: m000004oauthtables.
 */
class mwmod_mw_dbcore_m000005oauthclientsrestructure extends mwmod_mw_db_migrations_itemabs {

	function get_description() {
		return "Restructure oauth_clients (int id + client_id column)";
	}

	function check() {
		// Needs applying while the table exists but lacks the client_id column.
		return $this->table_exists("oauth_clients")
			&& !$this->column_exists("oauth_clients", "client_id");
	}

	function apply() {
		return $this->run_sql("
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
		");
	}

}
