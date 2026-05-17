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
		return (int) $this->get_data_field("user_id");
	}

	/** @return string SHA-256 hash of the raw token */
	function getTokenHash() {
		return $this->get_data_field("token_hash");
	}

	/** @return string */
	function getLabel() {
		return $this->get_data_field("label");
	}

	/** @return bool */
	function isActive() {
		return (bool) $this->get_data_field("active");
	}

	/** @return string */
	function getCreatedAt() {
		return $this->get_data_field("created_at");
	}

	/** @return string|null */
	function getLastUsedAt() {
		return $this->get_data_field("last_used_at");
	}

	/** @return string|null NULL means never expires */
	function getExpiresAt() {
		return $this->get_data_field("expires_at");
	}

	// ============================================
	// Permission helpers
	// ============================================

	/**
	 * Get the decoded permissions array.
	 * Returns null if permissions_json is NULL (= no restriction).
	 * Returns an empty array if JSON is empty or malformed (= restrict all).
	 *
	 * @return string[]|null
	 */
	function getPermissions() {
		if ($this->permissionsCache !== null) {
			return $this->permissionsCache;
		}
		$raw = $this->get_data_field("permissions_json");
		if ($raw === null || $raw === "") {
			return null;  // no restriction
		}
		$decoded = json_decode($raw, true);
		$this->permissionsCache = is_array($decoded) ? $decoded : [];
		return $this->permissionsCache;
	}

	/**
	 * Check whether this token allows a specific permission code.
	 *
	 * Rules:
	 * - permissions_json IS NULL  → token allows everything (no restriction)
	 * - permissions_json is a JSON array → token only allows listed codes
	 *
	 * @param string $code
	 * @return bool
	 */
	function allowsPermission($code) {
		$perms = $this->getPermissions();
		if ($perms === null) {
			return true;  // no restriction
		}
		return in_array($code, $perms, true);
	}

	// ============================================
	// Mutation helpers
	// ============================================

	/**
	 * Update last_used_at to now (lightweight direct query, no full item reload).
	 * @return void
	 */
	function touchLastUsed() {
		if (!$tbl = $this->man->get_tblman()) {
			return;
		}
		$tbl->update_item_by_id(
			$this->get_id(),
			["last_used_at" => date("Y-m-d H:i:s")]
		);
	}

	/**
	 * Revoke this token immediately.
	 * @return bool
	 */
	function revoke() {
		return $this->set_data_and_save(["active" => 0]);
	}
}
?>
