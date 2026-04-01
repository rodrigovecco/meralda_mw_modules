<?php
/**
 * Users2 - Concrete Manager
 * 
 * Default implementation of users2 manager.
 * Extend to customize behavior.
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 */
class mwmod_mw_users2_usersman extends mwmod_mw_users2_usersmanabs {

    /**
     * Constructor
     * 
     * @param mixed $ap Application point
     * @param string $tbl Users table name
     * @param string $sessionvar Session variable name
     */
    public function __construct($ap, string $tbl = "users", string $sessionvar = "__current_user_data") {
        parent::__construct($ap, $tbl, $sessionvar);
    }

    /**
     * Create password policy with secure default values
     */
    public function create_pass_policy() {
        $man = new mwmod_mw_users2_passpolicy($this);
        return $man;
    }

    /**
     * Create default mailer
     */
    public function create_user_mailer() {
        $man = new mwmod_mw_users_usermailer_def($this);
        $man->set_msg_enabled("user_reset_pass_request");
        return $man;
    }

    /**
     * Create groups manager if needed
     */
    public function create_groups_man() {
        return new mwmod_mw_users_groups_man($this);
    }
}
