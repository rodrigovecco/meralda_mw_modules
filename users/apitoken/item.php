<?php
/**
 * User API Token Item
 * Represents a single row from user_api_tokens.
 *
 * The raw token string is never stored — only its SHA-256 hash.
 */
class mwmod_mw_users_apitoken_item extends mwmod_mw_manager_item {

	/** @var string[]|null Decoded permissions array; null = not yet decoded */
	private $permissionsCache = null;

	function __construct($tblitem, $man) {
		$this->init($tblitem, $man);
	}

	/** @return int */
	function getUserId() {
		return (int) $this->get_data("user_id");
	}

	/** @return string SHA-256 hash of the raw token */
	function getTokenHash() {
		return $this->get_data("token_hash");
	}

	/** @return string */
	function getLabel() {
		return $this->get_data("label");
	}

	/** @return bool */
	function isActive() {
		return (bool) $this->get_data("active");
	}

	/** @return string */
	function getCreatedAt() {
		return $this->get_data("created_at");
	}

	/** @return string|null */
	function getLastUsedAt() {
		return $this->get_data("last_used_at");
	}

	/** @return string|null NULL means never expires */
	function getExpiresAt() {
		return $this->get_data("expires_at");
	}

	// ============================================
	// Permission helpers
	// ============================================

	/**
	 * Get the decoded permissions array.
	 *
	 * New tokens always declare an explicit, non-empty scope. Legacy rows
	 * with NULL permissions_json (created before this rule was enforced)
	 * fall back to an empty array (= restrict all) to fail closed.
	 *
	 * @return string[]
	 */
	function getPermissions() {
		if ($this->permissionsCache !== null) {
			return $this->permissionsCache;
		}
		$raw = $this->get_data("permissions_json");
		if ($raw === null || $raw === "") {
			// Legacy / corrupted row → fail closed.
			$this->permissionsCache = [];
			return $this->permissionsCache;
		}
		$decoded = json_decode($raw, true);
		$this->permissionsCache = is_array($decoded) ? $decoded : [];
		return $this->permissionsCache;
	}

	/**
	 * Check whether this token allows a specific permission code.
	 *
	 * Tokens MUST declare every permission they need. A token with an empty
	 * scope (legacy NULL or malformed JSON) grants nothing.
	 *
	 * @param string $code
	 * @return bool
	 */
	function allowsPermission($code) {
		$perms = $this->getPermissions();
		if (empty($perms)) {
			return false;
		}
		return in_array($code, $perms, true);
	}

	// ============================================
	// Mutation helpers
	// ============================================

	/**
	 * Update last_used_at to now.
	 * @return void
	 */
	function touchLastUsed() {
		$this->do_save_data(["last_used_at" => date("Y-m-d H:i:s")]);
	}

	/**
	 * Revoke this token immediately.
	 * @return bool
	 */
	function revoke() {
		return $this->do_save_data(["active" => 0]);
	}
}
?>
