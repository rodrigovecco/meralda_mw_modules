<?php
/**
 * Users2 - Default Manager Configuration
 * 
 * @package mwmod_mw_users2
 * @subpackage def
 * @since 2026-03
 */
class mwmod_mw_users2_def_usersman extends mwmod_mw_users2_usersmanabs {

    public function __construct($ap, string $tbl = "users", string $sessionvar = "__current_user_data") {
        $this->init_tbl_mode($ap, $tbl, $sessionvar);
        $this->createRolsAndPermissions();
    }

    public function create_user_data_man() {
        $man = new mwmod_mw_users2_def_userdata($this);
        $man->admin_user_id_enabled = false;
        $man->user_must_change_password_enabled = true;
        return $man;
    }

    public function create_pass_policy() {
        $man = new mwmod_mw_users2_passpolicy($this);
        return $man;
    }

    public function create_user_mailer() {
        $man = new mwmod_mw_users_usermailer_def($this);
        $man->set_msg_enabled("user_reset_pass_request");
        return $man;
    }
}
