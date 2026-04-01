<?php
/**
 * Abstract base class for My Account subinterfaces.
 * Extends the modern users2 UI base with permission checking.
 */
class mwmod_mw_users2_ui_myaccount_abs extends mwmod_mw_users2_ui_abs {
    
    /**
     * Check permission to edit own data.
     * 
     * @return bool
     */
    function is_allowed(): bool {
        return $this->allow("editmydata");
    }
    
    /**
     * Loads JS dependencies for modern inputs.
     */
    function prepare_before_exec_no_sub_interface(): void {
        $this->loadModernInputsJs();
    }
}
?>
