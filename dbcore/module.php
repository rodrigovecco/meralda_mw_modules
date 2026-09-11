<?php
/**
 * Meralda core migration module (PHP objects).
 *
 * Holds the base framework schema migrations, kept separate from the migration
 * engine (modules/mw/db/migrations) and from app-level db modules.
 *
 * Registered as the first module by the migration manager constructor. Uses the
 * "meralda" code so the existing per-module version state (state_meralda) is
 * preserved for installs upgraded from the previous SQL-file scheme.
 *
 * Items run in declaration order.
 */
class mwmod_mw_dbcore_module extends mwmod_mw_db_migrations_moduleabs {

	function get_code() {
		return "meralda";
	}

	function get_description() {
		return "Meralda core";
	}

	function load_items() {
		$this->add_item(new mwmod_mw_dbcore_m000001users());
		$this->add_item(new mwmod_mw_dbcore_m000002bruteforce());
		$this->add_item(new mwmod_mw_dbcore_m000003userapitokens());
		$this->add_item(new mwmod_mw_dbcore_m000004oauthtables());
		$this->add_item(new mwmod_mw_dbcore_m000005oauthclientsrestructure());
	}

}
