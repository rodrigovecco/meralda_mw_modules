<?php
/**
 * Core migration 000002: bruteforce protection tables.
 */
class mwmod_mw_dbcore_m000002bruteforce extends mwmod_mw_db_migrations_itemabs {

	function get_description() {
		return "Bruteforce protection tables";
	}

	function check() {
		return !$this->table_exists("bruteforce_blacklist")
			|| !$this->table_exists("bruteforce_ip_activity")
			|| !$this->table_exists("bruteforce_whitelist");
	}

	function apply() {
		return $this->run_sql("
			CREATE TABLE IF NOT EXISTS `bruteforce_blacklist` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `ip_address` varchar(45) NOT NULL,
			  `reason` varchar(255) DEFAULT NULL,
			  `banned_on` datetime NOT NULL DEFAULT current_timestamp(),
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `ip_address` (`ip_address`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

			CREATE TABLE IF NOT EXISTS `bruteforce_ip_activity` (
			  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `ip_address` varchar(45) DEFAULT NULL,
			  `last_username_attempted` varchar(255) DEFAULT NULL,
			  `failed_attempts` int(11) NOT NULL DEFAULT 0,
			  `last_attempt` datetime NOT NULL DEFAULT current_timestamp(),
			  `lock_until` datetime DEFAULT NULL,
			  `historical_failed_attempts` int(11) NOT NULL DEFAULT 0,
			  `historical_successful_attempts` int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `ip_address` (`ip_address`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

			CREATE TABLE IF NOT EXISTS `bruteforce_whitelist` (
			  `id` int(20) NOT NULL AUTO_INCREMENT,
			  `ip_address` varchar(45) NOT NULL,
			  `description` varchar(255) DEFAULT NULL,
			  `added_on` datetime NOT NULL DEFAULT current_timestamp(),
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `ip_address` (`ip_address`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		");
	}

}
