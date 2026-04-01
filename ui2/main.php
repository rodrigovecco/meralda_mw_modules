<?php
/**
 * UI2 - Main interface base definition
 * 
 * Extends legacy ui def but uses users2 module for My Account.
 * Adds security enforcement layer: when the logged user has a pending
 * mandatory action (forced password change, future 2FA, etc.), only
 * subinterfaces that declare themselves compatible via
 * isAllowedDuringForcedSecurityAction() will be set as current.
 */
abstract class mwmod_mw_ui2_main extends mwmod_mw_uitemplates_sbadmin_main {
    
    /**
     * Code of the subinterface to force when user must change password.
     * @var string
     */
    protected string $forceChangePassSubinterfaceCode = 'forcechangepass';
	
	// ==========================================================================
	// @todo WIP - UI modernas de login/rememberlogindata EN PAUSA
	// Descomentar cuando se retome el trabajo en ui2/sub/uilogin.php y rememberlogindata.php
	// ==========================================================================
	// function create_subinterface_rememberlogindata(){
	// 	$si=new mwmod_mw_ui2_sub_rememberlogindata("rememberlogindata",$this);
	// 	return $si;
	// }
	// function create_subinterface_login(){
	// 	$si= new mwmod_mw_ui2_sub_uilogin("login",$this);
	// 	return $si;
	// }
	// ==========================================================================
	
    /**
     * Create My Account subinterface using users2 module
     */
    function create_subinterface_myaccount() {
        return new mwmod_mw_users2_ui_myaccount_myaccount("myaccount", $this);
    }

    /**
     * Create forced password change subinterface
     */
    function create_subinterface_forcechangepass() {
        return new mwmod_mw_users2_ui_forcechangepass("forcechangepass", $this);
    }

    /**
     * Hook: intercept subinterface assignment when a security action is pending.
     * 
     * If the user must complete a mandatory action (password change, 2FA, etc.),
     * and the requested subinterface does not declare itself compatible via
     * isAllowedDuringForcedSecurityAction(), it gets replaced by the forced one.
     */
    function onBeforeSetCurrentSubinterface($si) {
        if (!$forced_code = $this->getForcedSecuritySubinterfaceCode()) {
            return $si;
        }
        
        // If the SI declares itself exempt, let it through
        if ($si->isAllowedDuringForcedSecurityAction()) {
            return $si;
        }
        
        // Force the security subinterface instead
        if ($forced_si = $this->get_subinterface($forced_code)) {
            if ($forced_si->is_allowed()) {
                return $forced_si;
            }
        }
        
        return $si;
    }

    /**
     * Determine if the current user has a pending mandatory security action.
     * Returns the subinterface code to force, or null if none.
     * 
     * Override to add more checks (2FA, app authentication, etc.)
     * 
     * @return string|null Subinterface code to force, or null
     */
    protected function getForcedSecuritySubinterfaceCode(): ?string {
        if (!$user = $this->get_admin_current_user()) {
            return null;
        }
        
        if (method_exists($user, 'mustChangePassword') && $user->mustChangePassword()) {
            return $this->forceChangePassSubinterfaceCode;
        }
        
        // Future: 2FA, app auth, etc.
        
        return null;
    }
}
?>