<?php
/**
 * My Account — Tokens (container)
 *
 * Subinterface with child tabs conditionally enabled by available managers:
 *   - jwt → only if $user->man->jwtMan is available
 *   - api → only if $user->man->getApitokenMan() is available
 *
 * If only one is available, the framework shows it as the sole section.
 * If neither is available, is_allowed() returns false.
 */
class mwmod_mw_users2_ui_myaccount_apitokens extends mwmod_mw_ui_base_basesubuia {

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("tokens", "Tokens"));
		$this->mnuIconClass = "fa fa-key";

		// Build sucods dynamically based on available managers
		$codes = [];
		if ($user = $this->get_current_user()) {
			if ($user->man->jwtMan) {
				$codes[] = "jwt";
			}
			if ($user->man->getApitokenMan()) {
				$codes[] = "api";
			}
		}
		$this->sucods = implode(",", $codes);
		$this->subinterface_def_code = $codes[0] ?? "";
	}

	function _do_create_subinterface_child_jwt($cod) {
		return new mwmod_mw_users_ui_myaccount_apitokens_jwt($cod, $this);
	}

	function _do_create_subinterface_child_api($cod) {
		return new mwmod_mw_users_ui_myaccount_apitokens_api($cod, $this);
	}

	function do_exec_no_sub_interface() {
	}

	function do_exec_page_in() {
	}

	function is_allowed(): bool {
		if (!$this->allow("owntoken")) {
			return false;
		}
		if (!$user = $this->get_current_user()) {
			return false;
		}
		return $user->man->jwtMan || $user->man->getApitokenMan();
	}
}
?>
