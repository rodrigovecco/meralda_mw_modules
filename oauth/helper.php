<?php
/**
 * OAuth 2.1 helper utilities — Meralda core.
 *
 * Stateless helpers shared by the OAuth server, authorize endpoint and consent
 * UI: PKCE S256 verification, base64url encoding, random ID generation and
 * authorization-code hashing.
 *
 * Lives in the mw package so ANY Meralda project (not only mtsx) can reuse it.
 */
class mwmod_mw_oauth_helper
{
	/** Authorization code TTL in seconds (10 minutes). */
	const AUTH_CODE_TTL = 600;

	/** Maximum redirect URIs accepted on a single DCR request. */
	const MAX_REDIRECT_URIS = 5;

	/**
	 * base64url encode (RFC 4648, no padding).
	 *
	 * @param string $data
	 * @return string
	 */
	static function b64url($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * base64url decode.
	 *
	 * @param string $data
	 * @return string|false
	 */
	static function b64urlDecode($data)
	{
		$padded = $data . str_repeat('=', (4 - (strlen($data) % 4)) % 4);
		$decoded = base64_decode(strtr($padded, '-_', '+/'), true);
		return ($decoded !== false) ? $decoded : false;
	}

	/**
	 * Verify a PKCE S256 code_challenge against the code_verifier sent at token
	 * exchange time.
	 *
	 * @param string $verifier  Plain verifier sent by the client.
	 * @param string $challenge Stored challenge (base64url-SHA256 of verifier).
	 * @return bool
	 */
	static function verifyPkce($verifier, $challenge)
	{
		if (!is_string($verifier) || $verifier === '' || !is_string($challenge)) {
			return false;
		}
		// RFC 7636: 43-128 chars, [A-Z][a-z][0-9]-._~
		if (strlen($verifier) < 43 || strlen($verifier) > 128) {
			return false;
		}
		if (!preg_match('/^[A-Za-z0-9\-._~]+$/', $verifier)) {
			return false;
		}
		$expected = self::b64url(hash('sha256', $verifier, true));
		return hash_equals($expected, $challenge);
	}

	/**
	 * Generate a random client_id (40 hex chars = 160 bits of entropy).
	 *
	 * @return string
	 */
	static function generateClientId()
	{
		return bin2hex(random_bytes(20));
	}

	/**
	 * Hash an authorization code for storage. Only the SHA-256 is persisted so
	 * a DB leak cannot be replayed.
	 *
	 * @param string $code Plain code (shown once to the client).
	 * @return string 64 hex chars.
	 */
	static function hashCode($code)
	{
		return hash('sha256', $code);
	}

	/**
	 * Generate a plaintext authorization code (returned to the client once).
	 *
	 * @return string
	 */
	static function generateAuthCode()
	{
		return self::b64url(random_bytes(32));
	}
}
