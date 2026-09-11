<?php
/**
 * Core migration 000003: user API tokens.
 * Standalone access tokens for users, independent of password.
 */
class mwmod_mw_dbcore_m000003userapitokens extends mwmod_mw_db_migrations_itemabs {

	function get_description() {
		return "User API tokens";
	}

	function check() {
		return !$this->table_exists("user_api_tokens");
	}

	function apply() {
		// Semicolons inside COMMENT strings are handled by the SQL parser.
		return $this->run_sql("
			CREATE TABLE IF NOT EXISTS `user_api_tokens` (
			  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
			  `user_id`          INT           NOT NULL,
			  `token_hash`       CHAR(64)      NOT NULL,
			  `label`            VARCHAR(160)  NOT NULL,
			  `permissions_json` TEXT          NULL     COMMENT 'JSON array of permission codes (NULL = no extra restriction)',
			  `active`           TINYINT(1)    NOT NULL DEFAULT 1,
			  `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `last_used_at`     DATETIME      NULL,
			  `expires_at`       DATETIME      NULL     COMMENT 'NULL means never expires',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `uq_user_api_tokens_hash` (`token_hash`),
			  KEY `idx_user_api_tokens_user`   (`user_id`),
			  KEY `idx_user_api_tokens_active` (`active`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		");
	}

}
