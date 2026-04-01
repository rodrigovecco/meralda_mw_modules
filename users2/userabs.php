<?php
/**
 * Users2 - Modernized Abstract User
 * 
 * Extends mwmod_mw_users_user adding:
 * - PHP 7.4+ type hints
 * - Improved helper methods
 * - Better encapsulation
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 * 
 * @property-read int $id User ID
 * @property-read string $idname Username/email
 * @property-read mwmod_mw_users2_usersmanabs $man Users manager
 */
abstract class mwmod_mw_users2_userabs extends mwmod_mw_users_user {

    // =========================================================================
    // IDENTITY - WITH TYPE HINTS
    // =========================================================================

    /**
     * Get user ID
     */
    public function getId(): int {
        return (int) $this->get_id();
    }

    /**
     * Get username/email for login
     */
    public function getUsername(): string {
        return (string) $this->get_idname();
    }

    /**
     * Alias of getUsername for compatibility
     */
    public function getIdName(): string {
        return $this->getUsername();
    }

    /**
     * Get full name
     */
    public function getFullName(): string {
        return (string) ($this->get_real_name() ?: $this->getUsername());
    }

    /**
     * Get display name (full name or username)
     */
    public function getDisplayName(): string {
        return $this->get_real_name_or_idname();
    }

    /**
     * Get validated email
     * 
     * @return string|null Email or null if not set/invalid
     */
    public function getEmail(): ?string {
        $email = $this->get_email();
        return $email ?: null;
    }

    /**
     * Get personal (secondary) email
     */
    public function getPersonalEmail(): ?string {
        $email = $this->get_personal_email();
        return $email ?: null;
    }

    // =========================================================================
    // STATUS
    // =========================================================================

    /**
     * Is user active?
     */
    public function isActive(): bool {
        return (bool) $this->is_active();
    }

    /**
     * Is main system administrator?
     */
    public function isMainAdmin(): bool {
        return (bool) $this->is_main_user();
    }

    /**
     * Can user login?
     */
    public function canLogin(): bool {
        return (bool) $this->can_login();
    }

    /**
     * Is out of office?
     */
    public function isOutOfOffice(): bool {
        return (bool) $this->is_out_of_office();
    }

    /**
     * Get replacement user ID (out of office)
     */
    public function getReplacementUserId(): ?int {
        $id = $this->get_out_of_office_replacement_id();
        return $id ? (int) $id : null;
    }

    /**
     * Must change password on next login?
     */
    public function mustChangePassword(): bool {
        return (bool) $this->get_data("must_change_pass");
    }

    // =========================================================================
    // ROLES AND PERMISSIONS
    // =========================================================================

    /**
     * Has the specified role?
     * 
     * @param string $roleCode Role code (e.g.: "admin", "user")
     */
    public function hasRole(string $roleCode): bool {
        return (bool) $this->has_rol_code($roleCode);
    }

    /**
     * Has any of the specified roles?
     * 
     * @param array $roleCodes List of role codes
     */
    public function hasAnyRole(array $roleCodes): bool {
        foreach ($roleCodes as $code) {
            if ($this->hasRole($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Has all specified roles?
     * 
     * @param array $roleCodes List of role codes
     */
    public function hasAllRoles(array $roleCodes): bool {
        foreach ($roleCodes as $code) {
            if (!$this->hasRole($code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get user's role list
     * 
     * @return array<string, mwmod_mw_users_rols_rol>
     */
    public function getRoles(): array {
        return $this->get_rols() ?: [];
    }

    /**
     * Get role codes as simple array
     * 
     * @return string[]
     */
    public function getRoleCodes(): array {
        $roles = $this->getRoles();
        return array_keys($roles);
    }

    /**
     * Has the specified permission?
     * 
     * @param string $permissionCode Permission code
     * @param mixed $params Additional parameters
     */
    public function hasPermission(string $permissionCode, $params = null): bool {
        return (bool) $this->allow($permissionCode, $params);
    }

    /**
     * Has any of the specified permissions? (OR)
     */
    public function hasAnyPermission(array $permissionCodes, $params = null): bool {
        foreach ($permissionCodes as $code) {
            if ($this->hasPermission($code, $params)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Has all specified permissions? (AND)
     */
    public function hasAllPermissions(array $permissionCodes, $params = null): bool {
        foreach ($permissionCodes as $code) {
            if (!$this->hasPermission($code, $params)) {
                return false;
            }
        }
        return true;
    }

    // =========================================================================
    // GROUPS
    // =========================================================================

    /**
     * Get user's groups
     * 
     * @return array<int, mwmod_mw_users_groups_item>
     */
    public function getGroups(): array {
        return $this->get_groups() ?: [];
    }

    /**
     * Belongs to specified group?
     * 
     * @param int $groupId Group ID
     */
    public function belongsToGroup(int $groupId): bool {
        $groups = $this->getGroups();
        return isset($groups[$groupId]);
    }

    /**
     * Get group IDs as array
     * 
     * @return int[]
     */
    public function getGroupIds(): array {
        return array_keys($this->getGroups());
    }

    // =========================================================================
    // DATA
    // =========================================================================

    /**
     * Get user data field
     * 
     * @param string $field Field name
     * @return mixed Field value or null
     */
    public function getField(string $field) {
        if ($field === 'pass' || $field === 'password') {
            return null; // Never expose password
        }
        return $this->get_data($field);
    }

    /**
     * Get all public data (without password)
     * 
     * @return array
     */
    public function getPublicData(): array {
        return $this->get_public_tbl_data() ?: [];
    }

    /**
     * Get data for APIs/JSON usage
     */
    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'full_name' => $this->getFullName(),
            'email' => $this->getEmail(),
            'is_active' => $this->isActive(),
            'is_main_admin' => $this->isMainAdmin(),
            'roles' => $this->getRoleCodes(),
            'groups' => $this->getGroupIds(),
        ];
    }

    /**
     * Serialize to JSON
     */
    public function toJson(): string {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // PROFILE IMAGES
    // =========================================================================

    /**
     * ¿Tiene imagen de perfil?
     */
    public function hasProfileImage(): bool {
        return (bool) $this->has_img();
    }

    /**
     * Obtener URL de imagen de perfil
     * 
     * @param string|null $size Tamaño: 'inline'|'tiny'|'small'|'profile'|'big' (null = default)
     */
    public function getProfileImageUrl(?string $size = null): ?string {
        $url = $this->get_img_url($size ?: false);
        return $url ?: null;
    }

    // =========================================================================
    // TOKENS Y RESET PASSWORD
    // =========================================================================

    /**
     * ¿Puede resetear contraseña?
     */
    public function canResetPassword(): bool {
        return (bool) $this->can_reset_pass();
    }

    /**
     * Verificar token de reset de password
     */
    public function verifyResetToken(string $token): bool {
        return (bool) $this->check_reset_pass_token($token);
    }

    // =========================================================================
    // EVENTOS (HOOKS)
    // =========================================================================

    /**
     * Evento ejecutado al hacer login
     * Extiende para agregar lógica personalizada
     */
    public function on_login() {
        parent::on_login();
        $this->onAfterLogin();
    }

    /**
     * Hook post-login para extender
     */
    protected function onAfterLogin(): void {
        // Override en subclases para agregar lógica
    }
}
