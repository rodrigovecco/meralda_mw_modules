<?php
/**
 * OAuth stateless token helper — Meralda core.
 *
 * Mints and verifies stateless OAuth access/refresh tokens derived from a
 * master user_api_token. No extra DB tables for tokens: the HMAC key is
 * user_api_tokens.token_hash (the SHA-256 of the original random bytes stored
 * in the existing table). Revoking the master API token immediately
 * invalidates every derived token.
 *
 * Lives in the mw package so ANY Meralda project can reuse it. Modules that
 * need a project-specific prefix or TTL simply subclass and override the
 * constants (see mwmod_mtsx_oauth_tokenhelper).
 *
 * Token format:  <prefix>.<b64url-payload>.<b64url-hmac>
 *   Access  prefix: "oa1"
 *   Refresh prefix: "or1"
 *
 * Payload (JSON): {"t":"a"|"r","tid":<token_db_id>,"uid":<user_id>,"exp":<unix>}
 *
 * HMAC message: "<prefix>.<b64url-payload>"  (signed WITH the prefix so the
 * same payload cannot be reused as a different token type).
 */
class mwmod_mw_oauth_tokenhelper
{
	const ACCESS_PREFIX  = 'oa1';
	const REFRESH_PREFIX = 'or1';
	const ACCESS_TTL     = 3600;

	/**
	 * Mint a short-lived access token bound to a master API token.
	 *
	 * @param int    $tokenId   user_api_tokens.id (jti of the master token).
	 * @param string $tokenHash user_api_tokens.token_hash (HMAC key).
	 * @param int    $userId    Owner user id.
	 * @return string
	 */
	static function createAccessToken($tokenId, $tokenHash, $userId)
	{
		return static::build(static::ACCESS_PREFIX, (int) $tokenId, $tokenHash, (int) $userId,
			time() + static::ACCESS_TTL);
	}

	/**
	 * Mint a refresh token with an explicit TTL.
	 *
	 * @param int    $tokenId
	 * @param string $tokenHash
	 * @param int    $userId
	 * @param int    $ttlSeconds
	 * @return string
	 */
	static function createRefreshToken($tokenId, $tokenHash, $userId, $ttlSeconds)
	{
		return static::build(static::REFRESH_PREFIX, (int) $tokenId, $tokenHash, (int) $userId,
			time() + (int) $ttlSeconds);
	}

	/**
	 * Assemble and sign a token.
	 */
	protected static function build($prefix, $tokenId, $tokenHash, $userId, $exp)
	{
		$b64payload = self::b64url(json_encode([
			't'   => ($prefix === static::ACCESS_PREFIX) ? 'a' : 'r',
			'tid' => $tokenId,
			'uid' => $userId,
			'exp' => $exp,
		]));
		$message = $prefix . '.' . $b64payload;
		$sig = self::b64url(hash_hmac('sha256', $message, $tokenHash, true));
		return $message . '.' . $sig;
	}

	/**
	 * Verify a token: shape, type, expiry, then HMAC against the live
	 * user_api_tokens row (authoritative revocation/expiry check).
	 *
	 * @param string             $token
	 * @param string             $expectedPrefix ACCESS_PREFIX or REFRESH_PREFIX.
	 * @param mwmod_mw_db_dbman  $db             DB manager (mainap->db).
	 * @return array{token_id:int,user_id:int,token_hash:string}|false
	 */
	static function verify($token, $expectedPrefix, $db)
	{
		if (!is_string($token) || $token === '') return false;

		$parts = explode('.', $token, 3);
		if (count($parts) !== 3) return false;

		list($prefix, $b64payload, $b64sig) = $parts;
		if ($prefix !== $expectedPrefix) return false;

		$payloadJson = self::b64urlDecode($b64payload);
		if ($payloadJson === false) return false;

		$payload = json_decode($payloadJson, true);
		if (!is_array($payload)) return false;

		$exp  = isset($payload['exp']) ? (int) $payload['exp'] : 0;
		$tid  = isset($payload['tid']) ? (int) $payload['tid'] : 0;
		$uid  = isset($payload['uid']) ? (int) $payload['uid'] : 0;
		$type = isset($payload['t']) ? $payload['t'] : '';

		$expectedType = ($expectedPrefix === static::ACCESS_PREFIX) ? 'a' : 'r';
		if ($type !== $expectedType || !$exp || !$tid || !$uid) return false;
		if ($exp <= time()) return false;

		$row = self::loadTokenRow($db, $tid);
		if (!$row) return false;
		if (!$row['active']) return false;
		if ($row['expires_at'] !== null && $row['expires_at'] !== ''
			&& strtotime($row['expires_at']) <= time()) return false;
		if ((int) $row['user_id'] !== $uid) return false;

		$message = $prefix . '.' . $b64payload;
		$expectedSig = self::b64url(hash_hmac('sha256', $message, $row['token_hash'], true));
		if (!hash_equals($expectedSig, $b64sig)) return false;

		return [
			'token_id'   => $tid,
			'user_id'    => $uid,
			'token_hash' => $row['token_hash'],
		];
	}

	/**
	 * Load the minimal columns needed to verify a derived token.
	 */
	protected static function loadTokenRow($db, $id)
	{
		if (!$db) return false;
		$link = $db->get_link();
		if (!$link) return false;

		$stmt = $link->prepare(
			'SELECT user_id, token_hash, active, expires_at
			   FROM user_api_tokens
			  WHERE id = ?
			  LIMIT 1'
		);
		if (!$stmt) return false;

		$stmt->bind_param('i', $id);
		if (!$stmt->execute()) {
			$stmt->close();
			return false;
		}

		$result = $stmt->get_result();
		$row = $result ? $result->fetch_assoc() : false;
		$stmt->close();
		return $row ?: false;
	}

	static function b64url($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	static function b64urlDecode($data)
	{
		$padded = $data . str_repeat('=', (4 - (strlen($data) % 4)) % 4);
		$decoded = base64_decode(strtr($padded, '-_', '+/'), true);
		return ($decoded !== false) ? $decoded : false;
	}
}
