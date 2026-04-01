<?php
/**
 * Users2 - Modernized Abstract Manager
 * 
 * Extends mwmod_mw_users_def_usersman adding:
 * - PHP 7.4+ type hints
 * - Improved helper methods
 * - Additional hooks for extension
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 * 
 * @property-read mwmod_mw_users_rols_rolsman|null $rolsMan
 * @property-read mwmod_mw_users_permissions_permissionsman|null $permissionsMan
 * @property-read mwmod_mw_users_groups_man|null $groupsMan
 * @property-read mwmod_mw_users_jwt_man|null $jwtMan
 */
abstract class mwmod_mw_users2_usersmanabs extends mwmod_mw_users_def_usersman {

    /**
     * Constructor with improved default configuration
     */
    public function __construct($ap, string $tbl = "users", string $sessionvar = "__current_user_data") {
        parent::__construct($ap, $tbl, $sessionvar);
        $this->applyDefaultSecuritySettings();
    }

    /**
     * Apply stricter default security settings
     */
    protected function applyDefaultSecuritySettings(): void {
        // Enable session token for login (CSRF protection)
        $this->enable_login_session_token(true);
        
        // Temporary lock after login failures
        $this->set_disable_login_after_fail(true, 10, 3);
    }

    // =========================================================================
    // USER RETRIEVAL METHODS WITH TYPE HINTS
    // =========================================================================

    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return mwmod_mw_users2_user|null User or null if not found
     */
    public function getUserById(int $id): ?object {
        return $this->get_user($id) ?: null;
    }

    /**
     * Get user by identification name (username/email)
     * 
     * @param string $idname Username or email
     * @return mwmod_mw_users2_user|null User or null if not found
     */
    public function getUserByIdName(string $idname): ?object {
        return $this->get_user_by_idname($idname) ?: null;
    }

    /**
     * Get currently logged in user
     * 
     * @return mwmod_mw_users2_user|null User or null if no session
     */
    public function getCurrentUser(): ?object {
        return $this->get_current_user() ?: null;
    }

    /**
     * Check if a user is logged in
     */
    public function isUserLogged(): bool {
        return $this->get_current_user() !== null && $this->get_current_user() !== false;
    }

    /**
     * Get all active users
     * 
     * @return array<int, mwmod_mw_users2_user>
     */
    public function getActiveUsers(): array {
        return $this->get_active_users() ?: [];
    }

    /**
     * Get users by role
     * 
     * @param string $rolCode Role code (e.g.: "admin", "user")
     * @return array<int, mwmod_mw_users2_user>
     */
    public function getUsersByRole(string $rolCode): array {
        return $this->get_users_rol($rolCode) ?: [];
    }

    // =========================================================================
    // IMPROVED AUTHENTICATION
    // =========================================================================

    /**
     * Login with additional validations
     * 
     * @param string $username
     * @param string $password
     * @param array $options Additional options: ['remember' => bool, 'skipBruteforce' => bool]
     * @return mwmod_mw_users2_user|null Logged in user or null if failed
     */
    public function authenticate(string $username, string $password, array $options = []): ?object {
        // Pre-login hook
        if (!$this->onBeforeLogin($username, $options)) {
            return null;
        }

        $user = $this->login($username, $password);

        if ($user) {
            // Successful post-login hook
            $this->onAfterLoginSuccess($user, $options);
            return $user;
        }

        // Failed login hook
        $this->onLoginFailed($username, $options);
        return null;
    }

    /**
     * Hook: Before attempting login
     * Override to add custom validations (captcha, rate limit, etc.)
     * 
     * @return bool True to continue, false to cancel login
     */
    protected function onBeforeLogin(string $username, array $options): bool {
        return true;
    }

    /**
     * Hook: After successful login
     * Override to add logging, notifications, etc.
     */
    protected function onAfterLoginSuccess(object $user, array $options): void {
        // Override in subclasses
    }

    /**
     * Hook: After failed login
     * Override to add security logging, alerts, etc.
     */
    protected function onLoginFailed(string $username, array $options): void {
        // Override in subclasses
    }

    /**
     * Logout current user
     */
    public function logoutCurrentUser(): void {
        $user = $this->getCurrentUser();
        
        if ($user) {
            $this->onBeforeLogout($user);
        }
        
        $this->logout();
        
        if ($user) {
            $this->onAfterLogout($user);
        }
    }

    /**
     * Hook: Before logout
     */
    protected function onBeforeLogout(object $user): void {
        // Override in subclasses
    }

    /**
     * Hook: After logout
     */
    protected function onAfterLogout(object $user): void {
        // Override in subclasses
    }

    // =========================================================================
    // IMPROVED PERMISSIONS
    // =========================================================================

    /**
     * Check permission with support for multiple permissions (OR)
     * 
     * @param string|array $permissions Permission or list of permissions
     * @param mixed $params Additional parameters
     * @return bool
     */
    public function hasPermission($permissions, $params = null): bool {
        if (is_array($permissions)) {
            return $this->allow_list($permissions, $params);
        }
        return $this->allow($permissions, $params);
    }

    /**
     * Check that user has ALL specified permissions (AND)
     * 
     * @param array $permissions List of required permissions
     * @param mixed $params
     * @return bool
     */
    public function hasAllPermissions(array $permissions, $params = null): bool {
        foreach ($permissions as $permission) {
            if (!$this->allow($permission, $params)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get permissions list for current user
     * 
     * @return array<string, mwmod_mw_users_permissions_permission>
     */
    public function getCurrentUserPermissions(): array {
        $user = $this->getCurrentUser();
        if (!$user) {
            return [];
        }
        return $user->get_permissions() ?: [];
    }

    // =========================================================================
    // FACTORY METHODS - OVERRIDE TO USE users2 CLASSES
    // =========================================================================

    /**
     * Create user instance (override to use mwmod_mw_users2_user)
     */
    public function new_user($tblitem) {
        return new mwmod_mw_users2_user($this, $tblitem);
    }

    /**
     * Create userdata instance (override to use mwmod_mw_users2_userdata)
     */
    public function create_user_data_man() {
        $man = new mwmod_mw_users2_userdata($this);
        return $man;
    }

    // =========================================================================
    // UTILITIES
    // =========================================================================

    /**
     * Get debug info for current state
     */
    public function getDebugInfo(): array {
        return [
            'is_logged' => $this->isUserLogged(),
            'current_user_id' => $this->get_current_user_id(),
            'service_mode' => $this->ServiceMode,
            'session_token_enabled' => $this->login_session_token_enabled(),
            'brute_force_enabled' => $this->disable_login_after_fail_enabled(),
            'validation_done' => $this->userValidationDone,
            'params' => $this->get_data_params_debug_info(),
        ];
    }
}
