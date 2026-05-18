<?php
/**
 * My Account — Tokens (container)
 *
 * Subinterface with child tabs conditionally enabled by available managers:
 *   - jwt → only if $user->man->jwtMan is available
 *   - api → only if $user->man->getApitokenMan() is available
 *
 * Uses the dynamic creation pattern (allowcreatesubinterfacechildbycode +
 * _do_create_subinterface_child_<cod>) so each child is only instantiated
 * when actually requested.
 */
class mwmod_mw_users_ui_myaccount_apitokens extends mwmod_mw_ui_sub_uiabs {

	function __construct($cod, $parent) {
		$this->init_as_subinterface($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("tokens", "Tokens"));

		// Pick the first available child as default.
		if ($user = $this->get_current_user()) {
			if ($user->man->getJwtMan()) {
				$this->subinterface_def_code = "jwt";
			} else if ($user->man->getApitokenMan()) {
				$this->subinterface_def_code = "api";
			}
		}
	}

	function allowcreatesubinterfacechildbycode() {
		return true;
	}

	function _do_create_subinterface_child_jwt($cod) {
		if (!$user = $this->get_current_user()) {
			return false;
		}
		if (!$user->man->getJwtMan()) {
			return false;
		}
		return new mwmod_mw_users_ui_myaccount_apitokens_jwt($cod, $this);
	}

	function _do_create_subinterface_child_api($cod) {
		if (!$user = $this->get_current_user()) {
			return false;
		}
		if (!$user->man->getApitokenMan()) {
			return false;
		}
		return new mwmod_mw_users_ui_myaccount_apitokens_api($cod, $this);
	}

	function is_responsable_for_sub_interface_mnu() {
		return true;
	}

	function create_sub_interface_mnu_for_sub_interface($su = false) {
		$mnu = new mwmod_mw_mnu_mnu();
		if ($subs = $this->get_subinterfaces_by_code("jwt,api", true)) {
			foreach ($subs as $sub) {
				$sub->add_2_sub_interface_mnu($mnu);
			}
		}
		return $mnu;
	}

	function do_exec_no_sub_interface() {
	}

	function do_exec_page_in() {
	}

	function is_allowed() {
		if (!$this->allow("owntoken")) {
			return false;
		}
		if (!$user = $this->get_current_user()) {
			return false;
		}
		return $user->man->getJwtMan() || $user->man->getApitokenMan();
	}
}
?>
