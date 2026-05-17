<?php
/**
 * User API Token Manager
 * CRUD for user_api_tokens table.
 *
 * Tokens are independent of the user password and survive password changes.
 * Only the SHA-256 hash of the raw token is stored; the raw token is returned
 * once at creation time and never persisted.
 *
 * Permission codes are free-form strings (e.g. hbeat_read, hbeat_metrics_write).
 * When permissions_json is NULL the token imposes no additional restriction.
 * The super admin always passes all checks regardless of token scope.
 *
 * @extends mwmod_mw_manager_man<mwmod_mw_users_apitoken_item>
 */
class mwmod_mw_users_apitoken_man extends mwmod_mw_manager_man {

	function __construct($mainap) {
		$this->init("user_api_tokens", $mainap, "user_api_tokens");
	}

	/**
	 * @param mwmod_mw_db_row $tblitem
	 * @return mwmod_mw_users_apitoken_item
	 */
	function create_item($tblitem) {
		return new mwmod_mw_users_apitoken_item($tblitem, $this);
	}

	function get_item_name($item) {
		return $item->get_data("label");
	}

	// ============================================
	// Token creation
	// ============================================

	/**
	 * Generate a new API token and persist it.
	 * Returns an array with the plain-text token (show once) and the saved item.
	 *
	 * @param int      $userId
	 * @param string   $label
	 * @param string[] $permissions   Array of permission code strings. Empty array or
	 *                                omit for no restriction (NULL stored in DB).
	 * @param int|null $expiresInDays NULL = never expires
	 * @return array{token: string, item: mwmod_mw_users_apitoken_item}|false
	 */
	function createToken($userId, $label, $permissions = [], $expiresInDays = null) {
		$rawToken = bin2hex(random_bytes(32));
		$hash     = hash("sha256", $rawToken);

		$permJson = (!empty($permissions)) ? json_encode(array_values($permissions)) : null;
		$expiresAt = null;
		if ($expiresInDays !== null && $expiresInDays > 0) {
			$expiresAt = date("Y-m-d H:i:s", strtotime("+" . (int) $expiresInDays . " days"));
		}

		if (!$tbl = $this->get_tblman()) {
			return false;
		}
		$newId = $tbl->insert_item([
			"user_id"          => (int) $userId,
			"token_hash"       => $hash,
			"label"            => $label,
			"permissions_json" => $permJson,
			"active"           => 1,
			"expires_at"       => $expiresAt,
		]);
		if (!$newId) {
			return false;
		}
		$item = $this->get_item($newId);
		if (!$item) {
			return false;
		}
		return ["token" => $rawToken, "item" => $item];
	}

	// ============================================
	// Lookup
	// ============================================

	/**
	 * Find an active, non-expired token by its raw (unhashed) value.
	 * Updates last_used_at on hit.
	 *
	 * @param string $rawToken
	 * @return mwmod_mw_users_apitoken_item|false
	 */
	function findActiveByRawToken($rawToken) {
		if (!$rawToken || !is_string($rawToken)) {
			return false;
		}
		$hash = hash("sha256", $rawToken);

		if (!$tbl = $this->get_tblman()) {
			return false;
		}

		$sql = "SELECT * FROM user_api_tokens
				WHERE token_hash = '" . $tbl->escape($hash) . "'
				  AND active = 1
				  AND (expires_at IS NULL OR expires_at > NOW())
				LIMIT 1";

		$row = $tbl->fetch_assoc($tbl->new_query()->from->set_sql($sql));
		if (!$row) {
			return false;
		}

		$item = $this->get_item($row["id"]);
		if (!$item) {
			return false;
		}

		$item->touchLastUsed();
		return $item;
	}

	/**
	 * Get all tokens for a user
	 * @param int $userId
	 * @return array<int, mwmod_mw_users_apitoken_item>|false
	 */
	function getItemsByUser($userId) {
		return $this->get_items_by_simple_crit(["user_id" => (int) $userId]);
	}
}
?>
