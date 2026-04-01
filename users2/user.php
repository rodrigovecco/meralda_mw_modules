<?php
/**
 * Users2 - Concrete User
 * 
 * Default implementation of users2 user.
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 */
class mwmod_mw_users2_user extends mwmod_mw_users2_userabs {

    /**
     * Constructor
     * 
     * @param mwmod_mw_users2_usersmanabs $man Users manager
     * @param mixed $tblitem Database table item
     */
    public function __construct($man, $tblitem) {
        $this->init($man, $tblitem);
    }

    /**
     * Enable JSON storage per user
     */
    public function can_create_jsondata(): bool {
        return true;
    }

    /**
     * Enable tree storage per user
     */
    public function can_create_treedata(): bool {
        return true;
    }

    /**
     * Enable string storage per user
     */
    public function can_create_strdata(): bool {
        return true;
    }
}
