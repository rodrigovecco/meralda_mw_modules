<?php
/**
 * User API Token Manager
 * CRUD for user_api_tokens table.
 *
 * Tokens are signed JWTs using the same key as session JWTs but with
 * type=apitoken. The JWT payload contains: type, sub (user_id), jti (token DB
 * id), hash (SHA-256 of the original random bytes), permissions[], and exp
 * (if the token has an expiry). The raw JWT is returned once at creation and
 * never stored — only the hash is persisted.
 *
 * Validation (findActiveByJwt) verifies the JWT signature, then does a single
 * DB lookup by primary key (jti), then cross-checks hash, user_id, and
 * permissions between JWT and DB. Any mismatch is rejected.
 *
 * Permission codes MUST exist in the user manager's permission catalog.
 * Tokens cannot be created with an empty scope.
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

		// Sign a JWT that embeds the token identity, hash, and permissions.
		// The JWT is the bearer credential; the raw random bytes are discarded.
		if (!$jwtMan = $this->usersMan->getJwtMan()) {
			return false;
		}
		$jwtPayload = [
			"type"        => "apitoken",
			"sub"         => (int) $userId,
			"jti"         => $item->get_id(),
			"hash"        => $hash,
			"permissions" => $permissions,
		];
		if ($expiresAt !== null) {
			$jwtPayload["exp"] = strtotime($expiresAt);
		}
		if (!$jwt = $jwtMan->createToken($jwtPayload)) {
			return false;
		}
		return ["token" => $jwt, "item" => $item];
	}

	// ============================================
	// Lookup
	// ============================================

	/**
	 * Find an active, non-expired token from a signed API-token JWT.
	 *
	 * Steps:
	 *   1. Verify JWT signature (no DB hit).
	 *   2. Confirm type=apitoken (rejects session JWTs).
	 *   3. Single DB lookup by primary key (jti claim).
	 *   4. Cross-verify user_id, hash, and permissions between JWT and DB.
	 *
	 * @param string $jwtToken
	 * @return mwmod_mw_users_apitoken_item|false
	 */
	function findActiveByJwt($jwtToken) {
		if (!$jwtToken || !is_string($jwtToken)) {
			return false;
		}
		if (!$jwtMan = $this->usersMan->getJwtMan()) {
			return false;
		}

		// Verify signature and decode — also checks exp claim if present.
		if (!$payload = $jwtMan->validateToken($jwtToken)) {
			return false;
		}

		// Reject session JWTs routed here by mistake.
		if (($payload["type"] ?? null) !== "apitoken") {
			return false;
		}

		$tokenId    = $payload["jti"]         ?? null;
		$userId     = $payload["sub"]         ?? null;
		$hash       = $payload["hash"]        ?? null;
		$jwtPerms   = $payload["permissions"] ?? null;

		if (!$tokenId || !$userId || !$hash || !is_array($jwtPerms)) {
			return false;
		}

		// Single DB lookup by primary key.
		$item = $this->get_item((int) $tokenId);
		if (!$item) {
			return false;
		}

		// DB-side revocation and expiry check (authoritative).
		if (!$item->isActive()) {
			return false;
		}
		if ($exp = $item->getExpiresAt()) {
			if (strtotime($exp) <= time()) {
				return false;
			}
		}

		// Cross-verify JWT claims against DB values.
		if ($item->getUserId() !== (int) $userId) {
			return false;
		}
		if ($item->getTokenHash() !== $hash) {
			return false;
		}

		// Permissions must match exactly: protects against stale JWTs after
		// a scope change in the DB.
		$dbPerms = $item->getPermissions();
		sort($dbPerms);
		sort($jwtPerms);
		if ($dbPerms !== $jwtPerms) {
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
