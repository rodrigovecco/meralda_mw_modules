<?php
/**
 * User API Token Manager
 * CRUD for user_api_tokens table.
 *
 * Tokens are independent of the user password and survive password changes.
 * Only the SHA-256 hash of the raw token is stored; the raw token is returned
 * once at creation time and never persisted.
 *
 * Permission codes are free-form strings (e.g. hbeat_read, hbeat_metrics_write)
 * but MUST exist in the user manager's permission catalog
 * (`$usersMan->get_permission_man()->get_items()`). Tokens cannot be created
 * with an empty scope. The super admin always passes all checks regardless of
 * token scope.
 *
 * @extends mwmod_mw_manager_man<mwmod_mw_users_apitoken_item>
 */
class mwmod_mw_users_apitoken_man extends mwmod_mw_manager_man {

	/** @var mwmod_mw_users_base_usersmanabs */
	private $usersMan;

	function __construct($usersMan) {
		$this->usersMan = $usersMan;
		$this->init("user_api_tokens", $usersMan->mainap, "user_api_tokens");
	}

	final function __get_priv_usersMan(){
		return $this->usersMan;
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
	 * Security: permissions are MANDATORY. An empty list is rejected; tokens
	 * must always declare an explicit scope.
	 *
	 * @param int      $userId
	 * @param string   $label
	 * @param string[] $permissions   Non-empty array of permission code strings.
	 * @param int|null $expiresInDays NULL = never expires
	 * @return array{token: string, item: mwmod_mw_users_apitoken_item}|false
	 */
	function createToken($userId, $label, $permissions = [], $expiresInDays = null) {
		// Reject tokens without an explicit permission scope.
		if (!is_array($permissions) || empty($permissions)) {
			return false;
		}

		// Validate every requested code against the declared permission catalog
		// so callers cannot smuggle arbitrary strings into the scope.
		if (!$permissionsMan = $this->usersMan->get_permission_man()) {
			return false;
		}
		$validCodes = [];
		if ($declared = $permissionsMan->get_items()) {
			foreach ($declared as $perm) {
				$validCodes[$perm->get_code()] = true;
			}
		}
		if (empty($validCodes)) {
			return false;
		}
		$clean = [];
		foreach ($permissions as $code) {
			if (!is_string($code) || $code === "") {
				return false;
			}
			if (!isset($validCodes[$code])) {
				return false;
			}
			$clean[$code] = true;
		}
		$permissions = array_keys($clean);

		$rawToken = bin2hex(random_bytes(32));
		$hash     = hash("sha256", $rawToken);

		$permJson = json_encode(array_values($permissions));
		$expiresAt = null;
		if ($expiresInDays !== null && $expiresInDays > 0) {
			$expiresAt = date("Y-m-d H:i:s", strtotime("+" . (int) $expiresInDays . " days"));
		}

		$item = $this->insert_item([
			"user_id"          => (int) $userId,
			"token_hash"       => $hash,
			"label"            => $label,
			"permissions_json" => $permJson,
			"active"           => 1,
			"expires_at"       => $expiresAt,
		]);
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
		if (!$query = $tbl->new_query()) {
			return false;
		}
		$query->where->add_where_crit("token_hash", $hash);
		$query->where->add_where_crit("active", 1);
		$query->where->add_where("(expires_at IS NULL OR expires_at > NOW())");
		$row = $query->get_one_row_result();
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
