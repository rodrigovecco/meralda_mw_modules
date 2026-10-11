<?php
/**
 * Reversible symmetric encryption (AES-256-CBC) for values that must be stored
 * AND read back later.
 *
 * Use this when a stored secret has to be recoverable — a value we need to show
 * or re-export, like an issued token. If the requirement is only to VERIFY a
 * value rather than recover it, do not store it here: use
 * mwmod_mw_util_random plus a hash (sha256), which cannot leak the plaintext.
 *
 * Scheme
 *   key   = sha256(secret) as raw bytes (32 bytes -> AES-256)
 *   iv    = 16 random bytes, unique per encryption
 *   store = base64(iv . ciphertext)
 *
 * Because the IV is random, encrypting the same plaintext twice yields
 * different ciphertext. That is the point, but it has a consequence worth
 * knowing: an encrypted column CANNOT be searched by equality. Keep a separate
 * hash column for lookups.
 *
 * Each module should inject its OWN secret so the encryption domains stay
 * separate: a leaked secret must not expose another module's values.
 */
class mwmod_mw_crypto_aes {

	const CIPHER = 'AES-256-CBC';
	const IV_LENGTH = 16;

	/** @var string|false */
	private $secret;

	/** @var bool Whether $secret already holds raw key bytes instead of a secret. */
	private $rawKey = false;

	/**
	 * @param string|false $secret Raw secret string. An empty/false value
	 *                             disables the instance (isEnabled() = false).
	 */
	function __construct($secret) {
		$this->secret = ($secret === false || $secret === null || $secret === '') ? false : (string) $secret;
	}

	/**
	 * Creates an instance from raw key bytes instead of a secret string.
	 * Useful when the key comes from a binary source; the bytes are used
	 * as-is, without hashing.
	 *
	 * @param string|false $key 32 raw bytes.
	 * @return self
	 */
	static function fromRawKey($key) {
		$o = new self(false);
		if (is_string($key) && $key !== '') {
			$o->secret = $key;
			$o->rawKey = true;
		}
		return $o;
	}

	/** Whether a usable secret is configured. */
	function isEnabled() {
		return $this->secret !== false;
	}

	/**
	 * Derives the AES key.
	 * @return string 32 raw bytes
	 */
	private function key() {
		if ($this->rawKey) {
			// Already a key: normalise to 32 bytes without hashing.
			return substr(str_pad($this->secret, 32, "\0"), 0, 32);
		}
		return hash('sha256', $this->secret, true);
	}

	/**
	 * Encrypts a value.
	 *
	 * @param string $plaintext
	 * @return string|false base64(iv . ciphertext), or false when disabled or
	 *                      when OpenSSL fails.
	 */
	function encrypt($plaintext) {
		if (!$this->isEnabled()) {
			return false;
		}
		$iv = random_bytes(self::IV_LENGTH);
		$ciphertext = openssl_encrypt((string) $plaintext, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv);
		if ($ciphertext === false) {
			return false;
		}
		return base64_encode($iv . $ciphertext);
	}

	/**
	 * Decrypts a value produced by encrypt().
	 *
	 * @param string $encoded
	 * @return string|false plaintext, or false when disabled or invalid.
	 */
	function decrypt($encoded) {
		if (!$this->isEnabled()) {
			return false;
		}
		$raw = base64_decode((string) $encoded, true);
		// One IV plus at least one cipher block.
		if ($raw === false || strlen($raw) < self::IV_LENGTH + 1) {
			return false;
		}
		$iv = substr($raw, 0, self::IV_LENGTH);
		$ciphertext = substr($raw, self::IV_LENGTH);
		$plaintext = openssl_decrypt($ciphertext, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv);
		return ($plaintext === false) ? false : $plaintext;
	}
}
?>
