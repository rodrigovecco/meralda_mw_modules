<?php
/**
 * Users2 - Modernized UserData
 * 
 * Handling forms, validation and saving user data.
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 */
class mwmod_mw_users2_userdata extends mwmod_mw_users_def_userdata {

    /**
     * Constructor
     */
    public function __construct($man) {
        parent::__construct($man);
        $this->applyDefaultSettings();
    }

    /**
     * Improved default configuration
     */
    protected function applyDefaultSettings(): void {
        // Enable first name + last name separate mode
        $this->firstAndLastNameMode = false;
        
        // Enable admin_user_id field (supervisor)
        $this->admin_user_id_enabled = false;
        
        // Enable "must change password" option
        $this->user_must_change_password_enabled = true;
    }

    // =========================================================================
    // IMPROVED VALIDATIONS
    // =========================================================================

    /**
     * Validate email with stricter rules
     * 
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email): bool {
        if (empty($email)) {
            return false;
        }
        
        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validate not temporary/disposable email (extensible)
        if (!$this->isAllowedEmailDomain($email)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if email domain is allowed
     * Override to add domain blacklist
     */
    protected function isAllowedEmailDomain(string $email): bool {
        // Base: allow all
        // Override in subclasses to add restrictions
        return true;
    }

    /**
     * Check if username already exists
     * 
     * @param string $username
     * @param int|null $excludeUserId Exclude this ID (for editing)
     * @return bool True if already exists
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool {
        $msg = '';
        return (bool) $this->user_already_exists($username, $excludeUserId, $msg);
    }

    // =========================================================================
    // EXTENSION HOOKS
    // =========================================================================

    /**
     * Hook: Before saving user data
     * 
     * @param array $data Data to save
     * @param object $user User
     * @return array Modified data
     */
    protected function onBeforeSave(array $data, $user): array {
        return $data;
    }

    /**
     * Hook: After saving user data
     */
    protected function onAfterSave($user): void {
        // Override in subclasses
    }

    /**
     * Hook: After creating new user
     */
    protected function onUserCreated($user): void {
        // Override in subclasses for:
        // - Send welcome email
        // - Create initial data
        // - Logging
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get fields not editable by user
     */
    public function getProtectedFields(): array {
        return [
            'id',
            'pass',
            'reset_pass_code',
            'reset_pass_enabled',
            'reset_pass_expires',
            'is_main',
            'last_login_date',
            'last_login_ip',
        ];
    }

    /**
     * Sanitize input data
     */
    public function sanitizeInput(array $data): array {
        $sanitized = [];
        $protected = $this->getProtectedFields();
        
        foreach ($data as $key => $value) {
            // Skip protected fields
            if (in_array($key, $protected)) {
                continue;
            }
            
            // Sanitize strings
            if (is_string($value)) {
                $sanitized[$key] = trim($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    // =========================================================================
    // MODERN JS INPUTS - My Account Forms
    // =========================================================================

    /**
     * Add inputs for My Account > Data form (modern JS inputs)
     * Equivalent to set_user_data_cr() but for mwmod_mw_jsobj_inputs_*
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addMyAccountDataInputs($mainGr, $user): void {
        // Username (read only)
        $input = $mainGr->addNewChild("username", "input");
        $input->setLabel($this->lng_get_msg_txt("user_name", "Nombre de usuario"));
        $input->set_value($user->get_idname());
        $input->setReadOnly(true);
        
        // Editable data group
        $dataGr = $mainGr->addNewGr("data");
        $this->addUserDataInputs($dataGr, $user);
        
        // Show admin user if assigned
        if ($idadmin = $user->get_data("admin_user_id")) {
            if ($adminu = $this->man->get_user($idadmin)) {
                $input = $mainGr->addNewChild("admin_user", "input");
                $input->setLabel($this->lng_get_msg_txt("manager", "Administrador"));
                $input->set_value($adminu->get_idname_and_real());
                $input->setReadOnly(true);
            }
        }
    }

    /**
     * Add editable user data inputs (extensible)
     * Equivalent to add_inputs_user_data() but for modern inputs
     * Override in def/userdata.php to add custom fields
     * 
     * @param mwmod_mw_jsobj_inputs_input $dataGr Data input group
     * @param object $user User object
     */
    public function addUserDataInputs($dataGr, $user): void {
        // Full name
        $input = $dataGr->addNewChild("complete_name", "input");
        $input->setLabel($this->lng_get_msg_txt("complete_name", "Nombre completo"));
        $input->set_value($user->get_data("complete_name"));
    }

    /**
     * Add inputs for My Account > Change Password form
     * Equivalent to set_user_changepass_cr() but for modern inputs
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addChangePassInputs($mainGr, $user): void {
        $passGr = $mainGr->addNewGr("pass");
        
        // Current password
        $input = $passGr->addNewChild("currentpass", "password");
        $input->setLabel($this->lng_get_msg_txt("current_password", "Contraseña actual"));
        $input->setRequired(true);
        
        // New password
        $inputPass = $passGr->addNewChild("pass", "password");
        $inputPass->setLabel($this->lng_get_msg_txt("password", "Contraseña"));
        $inputPass->setRequired(true);
        
        // Apply password policy validations
        $this->addPasswordPolicyValidations($inputPass);
        
        // Confirm password
        $inputPass1 = $passGr->addNewChild("pass1", "password");
        $inputPass1->setLabel($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
        $inputPass1->setRequired(true);
        
        // Password match validation using getParentChildByDotCodValue
        $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
        $validFnc = $inputPass1->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.getParentChildByDotCodValue(1,'pass','');\n");
        $validFnc->add_cont("var pass1=inputElem.get_input_value();\n");
        $validFnc->addCont("if(pass===pass1){return true}else{inputElem.set_validation_status_error('","[T]$msg","'); return false;}");
    }

    /**
     * Add inputs for Admin > Change User Password form (no current password required)
     * Used when admin changes another user's password.
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addAdminChangePassInputs($mainGr, $user): void {
        $passGr = $mainGr->addNewGr("pass");
        
        // New password (no current password - admin mode)
        $inputPass = $passGr->addNewChild("pass", "password");
        $inputPass->setLabel($this->lng_get_msg_txt("new_password", "Nueva contraseña"));
        $inputPass->setRequired(true);
        
        // Apply password policy validations
        $this->addPasswordPolicyValidations($inputPass);
        
        // Confirm password
        $inputPass1 = $passGr->addNewChild("pass1", "password");
        $inputPass1->setLabel($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
        $inputPass1->setRequired(true);
        
        // Password match validation
        $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
        $validFnc = $inputPass1->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.getParentChildByDotCodValue(1,'pass','');\n");
        $validFnc->add_cont("var pass1=inputElem.get_input_value();\n");
        $validFnc->addCont("if(pass===pass1){return true}else{inputElem.set_validation_status_error('","[T]$msg","'); return false;}");
    }

    /**
     * Add JS validations for password policy
     * 
     * @param mwmod_mw_jsobj_inputs_input $input Password input
     */
    protected function addPasswordPolicyValidations($input): void {
        $passPolicy = $this->man->get_pass_policy();
        if (!$passPolicy) {
            return;
        }
        
        // Get policy settings
        $minLen = $passPolicy->pass_min_len ?? 8;
        $maxLen = $passPolicy->pass_max_len ?? 100;
        $mustLowers = $passPolicy->must_contain_lowers ?? true;
        $mustUppers = $passPolicy->must_contain_uppers ?? true;
        $mustNumbers = $passPolicy->must_contain_numbers ?? true;
        
        // Build notes message
        $notes = [];
        $notes[] = $this->lng_get_msg_txt("password_min_length_note", "Mínimo %min% caracteres", ['min' => $minLen]);
        if ($mustLowers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_lowercase", "minúsculas");
        }
        if ($mustUppers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_uppercase", "mayúsculas");
        }
        if ($mustNumbers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_numbers", "números");
        }
        $input->setNotes(implode(", ", $notes));
        
        // Min length validation
        $msg = $this->lng_get_msg_txt("password_must_have_at_least_x_chars", "La contraseña debe tener al menos %n% caracteres", ['n' => $minLen]);
        $validFnc = $input->addValidation2List();
        $validFnc->add_cont("var validator=new mw_validator();\n");
        $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
        $validFnc->addCont("if(!validator.check_min_length(pass," . $minLen . ")){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
        $validFnc->add_cont("return true;");
        
        // Max length validation
        $msg = $this->lng_get_msg_txt("password_cant_have_more_than_x_chars", "La contraseña no debe tener más de %n% caracteres", ['n' => $maxLen]);
        $validFnc = $input->addValidation2List();
        $validFnc->add_cont("var validator=new mw_validator();\n");
        $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
        $validFnc->addCont("if(!validator.check_max_length(pass," . $maxLen . ")){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
        $validFnc->add_cont("return true;");
        
        // Lowercase validation
        if ($mustLowers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_lowercase", "La contraseña debe contener minúsculas");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->addCont("if(!validator.has_lowers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
        
        // Uppercase validation
        if ($mustUppers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_uppercase", "La contraseña debe contener mayúsculas");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->addCont("if(!validator.has_uppers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
        
        // Numbers validation
        if ($mustNumbers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_numbers", "La contraseña debe contener números");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->addCont("if(!validator.has_numbers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
    }

    // =========================================================================
    // MODERN JS INPUTS - New User Form
    // =========================================================================

    /**
     * Add inputs for Admin > New User form (modern JS inputs)
     * Equivalent to set_new_user_cr() but for mwmod_mw_jsobj_inputs_*
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     */
    public function addNewUserInputs($mainGr): void {
        // User data section
        $dataGr = $mainGr->addNewGr("data");
        
        // Username (email)
        $input = $dataGr->addNewChild("name", "input");
        $input->setLabel($this->lng_get_msg_txt("user_name", "Nombre de usuario"));
        $input->setRequired(true);
        $input->addValidationEmail();
        
        // Active checkbox
        $input = $dataGr->addNewChild("active", "checkbox");
        $input->setLabel($this->lng_get_msg_txt("active", "Activo"));
        $input->set_value(1); // Default to active
        
        // User data fields (complete_name, etc.)
        $this->addNewUserDataInputs($dataGr);
        
        // Admin user selector (if enabled)
        if ($this->admin_user_id_enabled) {
            $this->addAdminUserSelector($dataGr);
        }
        
        // Password section
        $this->addNewUserPasswordInputs($mainGr);
        
        // Roles section
        $this->addRolesInputs($mainGr);
        
        // Groups section
        $this->addGroupsInputs($mainGr);
    }

    /**
     * Add editable user data inputs for new user form
     * Override in def/userdata.php to add custom fields
     * 
     * @param mwmod_mw_jsobj_inputs_input $dataGr Data group
     */
    public function addNewUserDataInputs($dataGr): void {
        // Full name
        $input = $dataGr->addNewChild("complete_name", "input");
        $input->setLabel($this->lng_get_msg_txt("complete_name", "Nombre completo"));
    }

    /**
     * Add admin user selector input
     * 
     * @param mwmod_mw_jsobj_inputs_input $dataGr Data group
     */
    protected function addAdminUserSelector($dataGr): void {
        // TODO: Implement user selector when mwmod_mw_users2_util_seluserinput is available
        // For now, a simple input. Override in subclasses for full implementation.
        $input = $dataGr->addNewChild("admin_user_id", "input");
        $input->setLabel($this->lng_get_msg_txt("manager", "Administrador"));
        $input->setNotes($this->lng_get_msg_txt("admin_user_note", "ID del usuario administrador/supervisor"));
    }

    /**
     * Add password inputs for new user form
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     */
    public function addNewUserPasswordInputs($mainGr): void {
        $passPolicy = $this->man->get_pass_policy();
        
        // Password section
        $passGr = $mainGr->addNewGr("pass");
        $passGr->setTitleMode($this->lng_get_msg_txt("password", "Contraseña"));
        
        // Password
        $inputPass = $passGr->addNewChild("pass", "password");
        $inputPass->setLabel($this->lng_get_msg_txt("password", "Contraseña"));
        $inputPass->setRequired(true);
        
        // Apply password policy validations
        $this->addPasswordPolicyValidations($inputPass);
        
        // Confirm password
        $inputPass1 = $passGr->addNewChild("pass1", "password");
        $inputPass1->setLabel($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
        $inputPass1->setRequired(true);
        
        // Password match validation
        $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
        $validFnc = $inputPass1->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.getParentChildByDotCodValue(1,'pass','');\n");
        $validFnc->add_cont("var pass1=inputElem.get_input_value();\n");
        $validFnc->addCont("if(pass===pass1){return true}else{inputElem.set_validation_status_error('","[T]$msg","'); return false;}");
        
        // Secure password option (if pass_secure_mode === 2)
        if ($passPolicy && $passPolicy->pass_secure_mode === 2) {
            $input = $passGr->addNewChild("secpass", "checkbox");
            $input->setLabel($this->lng_get_msg_txt("secure_password", "Contraseña segura"));
            $input->set_value(1);
        }
        
        // Send email option (if can_send_email_on_create)
        if ($this->can_send_email_on_create()) {
            $input = $passGr->addNewChild("sendemail", "checkbox");
            $input->setLabel($this->lng_get_msg_txt("send_email", "Enviar correo"));
        }
        
        // Must change password option
        if ($this->user_must_change_password_enabled) {
            $input = $passGr->addNewChild("must_change_pass", "checkbox");
            $input->setLabel($this->lng_get_msg_txt("must_change_password", "Forzar cambio de contraseña en el siguiente log in del usuario"));
        }
    }

    /**
     * Add roles checkboxes for new user form
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     */
    public function addRolesInputs($mainGr): void {
        $rolsMan = $this->man->get_rols_man();
        if (!$rolsMan) {
            return;
        }
        
        $rolsMan->fix_tbl_fields();
        $rols = $rolsMan->get_assignable_items();
        if (!$rols || !sizeof($rols)) {
            return;
        }
        
        // Roles section
        $rolsGr = $mainGr->addNewGr("rols");
        $rolsGr->setTitleMode($this->lng_get_msg_txt("rols", "Roles"));
        
        foreach ($rols as $rolCod => $rol) {
            $rName = $rol->get_name();
            if ($desc = $rol->description) {
                $rName .= " - $desc";
            }
            
            $input = $rolsGr->addNewChild($rolCod, "checkbox");
            $input->setLabel($rName);
        }
    }

    /**
     * Add groups checkboxes for new user form
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object|null $user Optional existing user to pre-select groups
     */
    public function addGroupsInputs($mainGr, $user = null): void {
        $grMan = $this->man->get_groups_man();
        if (!$grMan) {
            return;
        }
        
        $items = $grMan->get_all_active_items();
        if (!$items || !sizeof($items)) {
            return;
        }
        
        // Groups section
        $groupsGr = $mainGr->addNewGr("groups");
        $groupsGr->setTitleMode($this->lng_get_msg_txt("groups", "Grupos"));
        
        foreach ($items as $id => $item) {
            $input = $groupsGr->addNewChild($id, "checkbox");
            $input->setLabel($item->get_name());
            
            // Pre-select if user belongs to this group
            if ($user && $item->contains_user($user)) {
                $input->set_value(1);
            }
        }
    }

    // =========================================================================
    // FORCED PASSWORD CHANGE (no current password required)
    // =========================================================================

    /**
     * Add inputs for forced password change form.
     * Only new password + confirmation, no current password.
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addForceChangePassInputs($mainGr, $user): void {
        $passGr = $mainGr->addNewGr("pass");
        
        // New password
        $inputPass = $passGr->addNewChild("pass", "password");
        $inputPass->setLabel($this->lng_get_msg_txt("new_password", "Nueva contraseña"));
        $inputPass->setRequired(true);
        
        // Apply password policy validations
        $this->addPasswordPolicyValidations($inputPass);
        
        // Confirm password
        $inputPass1 = $passGr->addNewChild("pass1", "password");
        $inputPass1->setLabel($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
        $inputPass1->setRequired(true);
        
        // Password match validation
        $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
        $validFnc = $inputPass1->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.getParentChildByDotCodValue(1,'pass','');\n");
        $validFnc->add_cont("var pass1=inputElem.get_input_value();\n");
        $validFnc->addCont("if(pass===pass1){return true}else{inputElem.set_validation_status_error('","[T]$msg","'); return false;}");
    }

    /**
     * Save forced password change (no current password check).
     * Validates new password pair, updates DB, clears must_change_pass.
     * 
     * @param mwmod_mw_helper_inputvalidator_request $input
     * @param object $user
     * @param mwmod_mw_html_elem|false $uimsgelem
     * @return bool
     */
    public function saveForceChangePass($input, $user, $uimsgelem = false): bool {
        if (!$input || !$input->is_req_input_ok()) {
            return false;
        }
        
        if (!$nd = $input->get_value_by_dot_cod_as_list("pass")) {
            return false;
        }
        if (!($nd["pass"] ?? null)) {
            return false;
        }
        
        $msg = "";
        if (!$pass = $this->check_passpair_input_by_array($nd, $msg)) {
            if ($msg && $uimsgelem) {
                $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
                $uimsgelem->add_cont($alert);
            }
            return false;
        }
        
        // Determine encryption
        if ($user->is_main()) {
            $sec = 1;
        } else {
            $sec = $user->get_password_is_encrypted();
        }
        
        $update = array();
        if ($sec) {
            $update["pass"] = $this->man->crypt_password($pass);
            $update["secpass"] = 1;
        } else {
            $update["pass"] = $pass;
            $update["secpass"] = 0;
        }
        $update["must_change_pass"] = 0;
        
        if (!$tblitem = $user->tblitem) {
            return false;
        }
        $tblitem->do_update($update);
        $user->set_new_password($pass);
        
        $msg = $this->lng_get_msg_txt("password_updated", "Contraseña actualizada");
        if ($uimsgelem) {
            $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg);
            $uimsgelem->add_cont($alert);
        }
        
        return true;
    }

    /**
     * Save user data from admin edit (editable fields only).
     * 
     * @param array $inputData Input data array
     * @param object $user User object  
     * @param mwmod_mw_html_elem|null $msgs Messages container
     * @return bool
     */
    public function saveUserData($inputData, $user, $msgs = null): bool {
        if (!$user || !$user->tblitem) {
            return false;
        }
        
        $update = [];
        
        // Complete name
        if (isset($inputData["complete_name"])) {
            $update["complete_name"] = trim($inputData["complete_name"]);
        }
        
        // Allow extension to add more fields
        $this->collectUserDataForSave($inputData, $update, $user);
        
        if (empty($update)) {
            // Nothing to update
            if ($msgs) {
                $msg = $this->lng_get_msg_txt("no_changes", "No hay cambios");
                $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "info");
                $msgs->add_cont($alert);
            }
            return true;
        }
        
        // Update in DB
        $user->tblitem->do_update($update);
        
        if ($msgs) {
            $msg = $this->lng_get_msg_txt("data_saved", "Datos guardados");
            $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg);
            $msgs->add_cont($alert);
        }
        
        return true;
    }

    /**
     * Extensible: collect additional user data for save
     * Override in def/userdata.php to handle custom fields
     * 
     * @param array $inputData Input data
     * @param array &$update Update array (by reference)
     * @param object $user User object
     */
    protected function collectUserDataForSave($inputData, &$update, $user): void {
        // Override in def/userdata.php
    }

    /**
     * Save password change from admin (no current password required).
     * 
     * @param array $inputData Input data with pass/pass1
     * @param object $user User object
     * @param mwmod_mw_html_elem|null $msgs Messages container
     * @return bool
     */
    public function saveAdminChangePass($inputData, $user, $msgs = null): bool {
        if (!$user || !$user->tblitem) {
            return false;
        }
        
        $pass = $inputData["pass"] ?? "";
        $pass1 = $inputData["pass1"] ?? "";
        
        if (!$pass || !$pass1) {
            if ($msgs) {
                $msg = $this->lng_get_msg_txt("fill_all_fields", "Complete todos los campos");
                $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
                $msgs->add_cont($alert);
            }
            return false;
        }
        
        if ($pass !== $pass1) {
            if ($msgs) {
                $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
                $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
                $msgs->add_cont($alert);
            }
            return false;
        }
        
        // Validate password policy
        $nd = ["pass" => $pass, "pass1" => $pass1];
        $msg = "";
        if (!$this->check_passpair_input_by_array($nd, $msg)) {
            if ($msgs && $msg) {
                $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
                $msgs->add_cont($alert);
            }
            return false;
        }
        
        // Determine encryption
        if ($user->is_main()) {
            $sec = 1;
        } else {
            $sec = $user->get_password_is_encrypted();
        }
        
        $update = [];
        if ($sec) {
            $update["pass"] = $this->man->crypt_password($pass);
            $update["secpass"] = 1;
        } else {
            $update["pass"] = $pass;
            $update["secpass"] = 0;
        }
        
        // Update in DB
        $user->tblitem->do_update($update);
        $user->set_new_password($pass);
        
        if ($msgs) {
            $msg = $this->lng_get_msg_txt("password_updated", "Contraseña actualizada");
            $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg);
            $msgs->add_cont($alert);
        }
        
        return true;
    }

    /**
     * Add inputs for user roles assignment (checkboxes)
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addUserRolsInputs($mainGr, $user): void {
        $rolsGr = $mainGr->addNewGr("rols");
        $rolsGr->setLabel($this->lng_get_msg_txt("rols", "Roles"));
        
        if (!$rolsman = $this->man->get_rols_man()) {
            return;
        }
        
        $rolsman->fix_tbl_fields();
        
        if ($rols = $rolsman->get_assignable_items()) {
            foreach ($rols as $rolcod => $rol) {
                $input = $rolsGr->addNewChild($rolcod, "checkbox");
                $input->setLabel($rol->get_name());
                
                // Check if user has this role
                if ($user->has_rol_code($rol->get_code())) {
                    $input->set_value(1);
                }
            }
        }
    }

    /**
     * Save user roles from admin edit
     * 
     * @param array $inputData Input data (role codes as keys)
     * @param object $user User object
     * @param mwmod_mw_html_elem|null $msgs Messages container
     * @return bool
     */
    public function saveUserRols($inputData, $user, $msgs = null): bool {
        if (!$user || !$user->tblitem) {
            return false;
        }
        
        if (!$rolsman = $this->man->get_rols_man()) {
            return false;
        }
        
        if (!$rols = $rolsman->get_assignable_items()) {
            return false;
        }
        
        $update = [];
        
        foreach ($rols as $rolcod => $rol) {
            if ($fieldcod = $rol->get_tbl_field_name()) {
                if (isset($inputData[$rolcod])) {
                    $update[$fieldcod] = $inputData[$rolcod] ? 1 : 0;
                } else {
                    // Unchecked checkboxes may not be sent
                    $update[$fieldcod] = 0;
                }
            }
        }
        
        if (!empty($update)) {
            $user->tblitem->do_update($update);
        }
        
        if ($msgs) {
            $msg = $this->lng_get_msg_txt("roles_saved", "Roles guardados");
            $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg);
            $msgs->add_cont($alert);
        }
        
        return true;
    }

    // =========================================================================
    // ADMIN FULL DATA FORM (Edit User - All Fields Combined)
    // =========================================================================

    /**
     * Add all inputs for admin full data edit form (modern JS inputs)
     * Equivalent to set_user_datafull_cr() but for mwmod_mw_jsobj_inputs_*
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    public function addFullDataInputs($mainGr, $user): void {
        // Access data section (username, active)
        $accessGr = $mainGr->addNewGr("accessdata");
        $accessGr->setTitleMode($this->lng_get_msg_txt("access_data", "Datos de acceso"));
        
        // Username (editable)
        $input = $accessGr->addNewChild("name", "input");
        $input->setLabel($this->lng_get_msg_txt("user_name", "Nombre de usuario"));
        $input->set_value($user->get_idname());
        $input->setRequired(true);
        $input->addValidationEmail();
        
        // Active checkbox
        $input = $accessGr->addNewChild("active", "checkbox");
        $input->setLabel($this->lng_get_msg_txt("active", "Activo"));
        $input->set_value($user->get_data("active") ? 1 : 0);
        
        // User data section (complete_name, etc.)
        $dataGr = $mainGr->addNewGr("data");
        $dataGr->setTitleMode($this->lng_get_msg_txt("user_data", "Datos del usuario"));
        $this->addUserDataInputs($dataGr, $user);
        
        // Admin user selector (if enabled)
        if ($this->admin_user_id_enabled) {
            $this->addAdminUserSelectorWithValue($dataGr, $user);
        }
        
        // Password section (optional change)
        $this->addEditPasswordInputs($mainGr, $user);
        
        // Roles section
        $this->addEditRolesInputs($mainGr, $user);
        
        // Groups section
        $this->addGroupsInputs($mainGr, $user);
    }

    /**
     * Add admin user selector with current value
     * 
     * @param mwmod_mw_jsobj_inputs_input $dataGr Data group
     * @param object $user User object
     */
    protected function addAdminUserSelectorWithValue($dataGr, $user): void {
        $input = $dataGr->addNewChild("admin_user_id", "input");
        $input->setLabel($this->lng_get_msg_txt("manager", "Administrador"));
        $input->set_value($user->get_data("admin_user_id"));
        $input->setNotes($this->lng_get_msg_txt("admin_user_note", "ID del usuario administrador/supervisor"));
    }

    /**
     * Add password inputs for edit user form (optional change)
     * Password is only updated if "change" checkbox is checked.
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    protected function addEditPasswordInputs($mainGr, $user): void {
        $passPolicy = $this->man->get_pass_policy();
        
        $passGr = $mainGr->addNewGr("pass");
        $passGr->setTitleMode($this->lng_get_msg_txt("change_password", "Modificar contraseña"));
        
        // New password
        $inputPass = $passGr->addNewChild("pass", "password");
        $inputPass->setLabel($this->lng_get_msg_txt("password", "Contraseña"));
        
        // Apply password policy validations (only when not empty)
        $this->addPasswordPolicyValidationsOptional($inputPass);
        
        // Confirm password
        $inputPass1 = $passGr->addNewChild("pass1", "password");
        $inputPass1->setLabel($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
        
        // Password match validation (only when pass1 not empty)
        $msg = $this->lng_get_msg_txt("passwords_not_match", "Las contraseñas no coinciden");
        $validFnc = $inputPass1->addValidation2List();
        $validFnc->add_cont("var pass1=inputElem.get_input_value();\n");
        $validFnc->add_cont("if(!pass1){return true;}\n"); // Skip if empty
        $validFnc->add_cont("var pass=inputElem.getParentChildByDotCodValue(1,'pass','');\n");
        $validFnc->addCont("if(pass===pass1){return true}else{inputElem.set_validation_status_error('","[T]$msg","'); return false;}");
        
        // Secure password option (if pass_secure_mode === 2)
        if ($passPolicy && $passPolicy->pass_secure_mode === 2) {
            if (!$user->is_main()) {
                $input = $passGr->addNewChild("secpass", "checkbox");
                $input->setLabel($this->lng_get_msg_txt("secure_password", "Contraseña segura"));
                $input->set_value($user->get_data("secpass") ? 1 : 0);
            }
        }
        
        // Must change password option
        if ($this->user_must_change_password_enabled) {
            $input = $passGr->addNewChild("must_change_pass", "checkbox");
            $input->setLabel($this->lng_get_msg_txt("must_change_password", "Forzar cambio de contraseña en el siguiente log in del usuario"));
            $input->set_value($user->get_data("must_change_pass") ? 1 : 0);
        }
        
        // Change password checkbox (triggers save)
        $input = $passGr->addNewChild("change", "checkbox");
        $input->setLabel($this->lng_get_msg_txt("change_password", "Modificar contraseña"));
    }

    /**
     * Add password policy validations (optional - only when field not empty)
     * 
     * @param mwmod_mw_jsobj_inputs_input $input Password input
     */
    protected function addPasswordPolicyValidationsOptional($input): void {
        $passPolicy = $this->man->get_pass_policy();
        if (!$passPolicy) {
            return;
        }
        
        // Get policy settings
        $minLen = $passPolicy->pass_min_len ?? 8;
        $maxLen = $passPolicy->pass_max_len ?? 100;
        $mustLowers = $passPolicy->must_contain_lowers ?? true;
        $mustUppers = $passPolicy->must_contain_uppers ?? true;
        $mustNumbers = $passPolicy->must_contain_numbers ?? true;
        
        // Build notes message
        $notes = [];
        $notes[] = $this->lng_get_msg_txt("password_min_length_note", "Mínimo %min% caracteres", ['min' => $minLen]);
        if ($mustLowers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_lowercase", "minúsculas");
        }
        if ($mustUppers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_uppercase", "mayúsculas");
        }
        if ($mustNumbers) {
            $notes[] = $this->lng_get_msg_txt("password_needs_numbers", "números");
        }
        $input->setNotes(implode(", ", $notes));
        
        // All validations skip if empty (optional change)
        
        // Min length validation
        $msg = $this->lng_get_msg_txt("password_must_have_at_least_x_chars", "La contraseña debe tener al menos %n% caracteres", ['n' => $minLen]);
        $validFnc = $input->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
        $validFnc->add_cont("if(!pass){return true;}\n"); // Skip if empty
        $validFnc->add_cont("var validator=new mw_validator();\n");
        $validFnc->addCont("if(!validator.check_min_length(pass," . $minLen . ")){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
        $validFnc->add_cont("return true;");
        
        // Max length validation
        $msg = $this->lng_get_msg_txt("password_cant_have_more_than_x_chars", "La contraseña no debe tener más de %n% caracteres", ['n' => $maxLen]);
        $validFnc = $input->addValidation2List();
        $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
        $validFnc->add_cont("if(!pass){return true;}\n");
        $validFnc->add_cont("var validator=new mw_validator();\n");
        $validFnc->addCont("if(!validator.check_max_length(pass," . $maxLen . ")){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
        $validFnc->add_cont("return true;");
        
        // Lowercase validation
        if ($mustLowers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_lowercase", "La contraseña debe contener minúsculas");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->add_cont("if(!pass){return true;}\n");
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->addCont("if(!validator.has_lowers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
        
        // Uppercase validation
        if ($mustUppers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_uppercase", "La contraseña debe contener mayúsculas");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->add_cont("if(!pass){return true;}\n");
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->addCont("if(!validator.has_uppers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
        
        // Numbers validation
        if ($mustNumbers) {
            $msg = $this->lng_get_msg_txt("password_must_contain_numbers", "La contraseña debe contener números");
            $validFnc = $input->addValidation2List();
            $validFnc->add_cont("var pass=inputElem.get_input_value()+'';\n");
            $validFnc->add_cont("if(!pass){return true;}\n");
            $validFnc->add_cont("var validator=new mw_validator();\n");
            $validFnc->addCont("if(!validator.has_numbers(pass)){inputElem.set_validation_status_error('","[T]$msg","');return false;}\n");
            $validFnc->add_cont("return true;");
        }
    }

    /**
     * Add roles checkboxes for edit user (with current values)
     * 
     * @param mwmod_mw_jsobj_inputs_input $mainGr Main input group
     * @param object $user User object
     */
    protected function addEditRolesInputs($mainGr, $user): void {
        $rolsMan = $this->man->get_rols_man();
        if (!$rolsMan) {
            return;
        }
        
        $rolsMan->fix_tbl_fields();
        $rols = $rolsMan->get_assignable_items();
        if (!$rols || !sizeof($rols)) {
            return;
        }
        
        $rolsGr = $mainGr->addNewGr("rols");
        $rolsGr->setTitleMode($this->lng_get_msg_txt("rols", "Roles"));
        
        foreach ($rols as $rolCod => $rol) {
            $rName = $rol->get_name();
            if ($desc = $rol->description) {
                $rName .= " - $desc";
            }
            
            $input = $rolsGr->addNewChild($rolCod, "checkbox");
            $input->setLabel($rName);
            
            // Check if user has this role
            if ($user->has_rol_code($rol->get_code())) {
                $input->set_value(1);
            }
        }
    }

    /**
     * Save full user data from admin edit form
     * 
     * @param mwmod_mw_helper_inputvalidator_request $input
     * @param object $user
     * @param mwmod_mw_html_elem $msgs
     * @return bool
     */
    public function saveFullData($input, $user, $msgs): bool {
        if (!$input || !$input->is_req_input_ok()) {
            return false;
        }
        
        $ok = true;
        
        // Save access data (username, active)
        if ($accessData = $input->get_value_by_dot_cod_as_list("accessdata")) {
            $ok = $this->saveAccessData($accessData, $user, $msgs) && $ok;
        }
        
        // Save user data (complete_name, etc.)
        if ($userData = $input->get_value_by_dot_cod_as_list("data")) {
            $ok = $this->saveUserData($userData, $user, $msgs) && $ok;
        }
        
        // Save password (if change checkbox is checked)
        if ($passData = $input->get_value_by_dot_cod_as_list("pass")) {
            if ($passData["change"] ?? false) {
                $ok = $this->saveAdminChangePass($passData, $user, $msgs) && $ok;
            }
            // Update must_change_pass flag
            if (isset($passData["must_change_pass"])) {
                $user->tblitem->do_update([
                    "must_change_pass" => $passData["must_change_pass"] ? 1 : 0
                ]);
            }
            // Update secpass if present
            if (isset($passData["secpass"])) {
                $user->tblitem->do_update([
                    "secpass" => $passData["secpass"] ? 1 : 0
                ]);
            }
        }
        
        // Save roles
        if ($rolsData = $input->get_value_by_dot_cod_as_list("rols")) {
            $ok = $this->saveUserRols($rolsData, $user, $msgs) && $ok;
        }
        
        // Save groups
        if ($groupsData = $input->get_value_by_dot_cod_as_list("groups")) {
            $this->save_user_groups($user, $groupsData);
        }
        
        return $ok;
    }

    /**
     * Save access data (username, active)
     * 
     * @param array $inputData
     * @param object $user
     * @param mwmod_mw_html_elem $msgs
     * @return bool
     */
    protected function saveAccessData($inputData, $user, $msgs): bool {
        if (!$user || !$user->tblitem) {
            return false;
        }
        
        $update = [];
        
        // Username change
        if (isset($inputData["name"])) {
            $newName = trim($inputData["name"]);
            if ($newName !== $user->get_idname()) {
                // Check if new username is available
                $msg = "";
                if ($this->user_already_exists($newName, $user->get_id(), $msg)) {
                    if ($msgs) {
                        $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
                        $msgs->add_cont($alert);
                    }
                    return false;
                }
                $update["name"] = $newName;
            }
        }
        
        // Active status
        if (isset($inputData["active"])) {
            $update["active"] = $inputData["active"] ? 1 : 0;
        }
        
        if (!empty($update)) {
            $user->tblitem->do_update($update);
        }
        
        return true;
    }
}
