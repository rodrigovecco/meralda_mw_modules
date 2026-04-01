<?php
/**
 * Users2 - Modernized Password Policy
 * 
 * Stricter default configuration and improved methods.
 * 
 * @package mwmod_mw_users2
 * @since 2026-03
 */
class mwmod_mw_users2_passpolicy extends mwmod_mw_users_passpolicy {

    /**
     * Constructor with secure default values
     */
    public function __construct($man) {
        parent::__construct($man);
        $this->applyStrictDefaults();
    }

    /**
     * Apply strict default configuration
     */
    protected function applyStrictDefaults(): void {
        // Minimum length 10 characters (more secure than 8)
        $this->pass_min_len = 10;
        $this->pass_max_len = 128;
        $this->pass_def_len = 14;
        
        // Require complexity
        $this->must_contain_uppers = true;
        $this->must_contain_lowers = true;
        $this->must_contain_numbers = true;
        
        // Always secure mode (hashed)
        $this->pass_secure_mode = 1;
        
        // Allow change in remember password UI
        $this->change_password_on_remember_ui_enabled = true;
    }

    // =========================================================================
    // ADDITIONAL VALIDATIONS
    // =========================================================================

    /** @var bool Require special characters */
    public bool $must_contain_special = false;

    /** @var array List of forbidden common passwords */
    protected array $commonPasswords = [
        'password', '123456789', 'qwerty123', 'admin123', 
        'letmein', 'welcome1', 'password1', 'changeme'
    ];

    /**
     * Validate complete password
     * 
     * @param string $password
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validatePassword(string $password): array {
        $errors = [];

        // Minimum length
        if (strlen($password) < $this->pass_min_len) {
            $errors[] = $this->lng_get_msg_txt(
                "password_must_have_at_least_x_chars",
                "Password must have at least %n% characters",
                ['n' => $this->pass_min_len]
            );
        }

        // Maximum length
        if (strlen($password) > $this->pass_max_len) {
            $errors[] = $this->lng_get_msg_txt(
                "password_cant_have_more_than_x_chars",
                "Password cannot have more than %n% characters",
                ['n' => $this->pass_max_len]
            );
        }

        // Uppercase
        if ($this->must_contain_uppers && !preg_match('/[A-Z]/', $password)) {
            $errors[] = $this->lng_get_msg_txt(
                "password_must_contain_uppercase",
                "Password must contain uppercase letters"
            );
        }

        // Lowercase
        if ($this->must_contain_lowers && !preg_match('/[a-z]/', $password)) {
            $errors[] = $this->lng_get_msg_txt(
                "password_must_contain_lowercase",
                "Password must contain lowercase letters"
            );
        }

        // Numbers
        if ($this->must_contain_numbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = $this->lng_get_msg_txt(
                "password_must_contain_numbers",
                "Password must contain numbers"
            );
        }

        // Special characters
        if ($this->must_contain_special && !preg_match('/[!@#$%^&*()_+\-=\[\]{};\'":\\|,.<>\/?]/', $password)) {
            $errors[] = $this->lng_get_msg_txt(
                "password_must_contain_special",
                "Password must contain special characters"
            );
        }

        // Common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = $this->lng_get_msg_txt(
                "password_too_common",
                "This password is too common"
            );
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if it's a common password
     */
    public function isCommonPassword(string $password): bool {
        $lower = strtolower($password);
        return in_array($lower, $this->commonPasswords, true);
    }

    /**
     * Add common passwords to blacklist
     */
    public function addCommonPasswords(array $passwords): void {
        $this->commonPasswords = array_merge(
            $this->commonPasswords, 
            array_map('strtolower', $passwords)
        );
    }

    /**
     * Generate secure password
     * 
     * @param int|null $length Length (null = use default)
     * @return string
     */
    public function generateSecurePassword(?int $length = null): string {
        $length = $length ?? $this->pass_def_len;
        
        $chars = [
            'lower' => 'abcdefghijklmnopqrstuvwxyz',
            'upper' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'number' => '0123456789',
            'special' => '!@#$%^&*()_+-=',
        ];
        
        $password = '';
        
        // Ensure at least one of each required type
        if ($this->must_contain_lowers) {
            $password .= $chars['lower'][random_int(0, strlen($chars['lower']) - 1)];
        }
        if ($this->must_contain_uppers) {
            $password .= $chars['upper'][random_int(0, strlen($chars['upper']) - 1)];
        }
        if ($this->must_contain_numbers) {
            $password .= $chars['number'][random_int(0, strlen($chars['number']) - 1)];
        }
        if ($this->must_contain_special) {
            $password .= $chars['special'][random_int(0, strlen($chars['special']) - 1)];
        }
        
        // Complete length with all allowed characters
        $allChars = implode('', $chars);
        $remaining = $length - strlen($password);
        
        for ($i = 0; $i < $remaining; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle characters
        return str_shuffle($password);
    }

    /**
     * Calculate password strength (0-100)
     */
    public function calculateStrength(string $password): int {
        $score = 0;
        $length = strlen($password);
        
        // Points for length
        $score += min(30, $length * 2);
        
        // Points for character types
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 15;
        if (preg_match('/[0-9]/', $password)) $score += 15;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;
        
        // Bonus for mixed types
        $types = 0;
        if (preg_match('/[a-z]/', $password)) $types++;
        if (preg_match('/[A-Z]/', $password)) $types++;
        if (preg_match('/[0-9]/', $password)) $types++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $types++;
        
        if ($types >= 3) $score += 10;
        if ($types >= 4) $score += 10;
        
        // Penalize common patterns
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 10; // Repeated characters
        if (preg_match('/^[a-z]+$|^[A-Z]+$|^[0-9]+$/', $password)) $score -= 20; // Single type only
        
        return max(0, min(100, $score));
    }

    /**
     * Get strength label
     */
    public function getStrengthLabel(int $score): string {
        if ($score < 25) return $this->lng_get_msg_txt("strength_very_weak", "Very weak");
        if ($score < 50) return $this->lng_get_msg_txt("strength_weak", "Weak");
        if ($score < 75) return $this->lng_get_msg_txt("strength_medium", "Medium");
        if ($score < 90) return $this->lng_get_msg_txt("strength_strong", "Strong");
        return $this->lng_get_msg_txt("strength_very_strong", "Very strong");
    }
}
