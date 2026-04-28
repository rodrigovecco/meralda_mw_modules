<?php
/**
 * Verifies Google Sign-In ID tokens (JWT) without third-party libraries.
 *
 * Replaces the legacy `Google_Client::verifyIdToken()` from
 * `modulesext-legacy/google/`, which depends on Guzzle 5/6 and is incompatible
 * with PHP 8.x and with the Guzzle 7 used by `modulesext/aws/`.
 *
 * Uses only PHP built-ins:
 *   - cURL to fetch Google's public certificates.
 *   - OpenSSL to verify the RSA-SHA256 signature.
 *
 * Two usage modes:
 *
 * 1) Static convenience (one-shot verification):
 *      $payload = mwmod_mw_google_idtoken::verify($jwt, $clientId);
 *
 * 2) Meralda instance mode (overridable, configurable):
 *      $v = new mwmod_mw_google_idtoken();
 *      $v->setClientId($clientId);
 *      $payload = $v->verifyToken($jwt);
 */
class mwmod_mw_google_idtoken extends mw_baseobj {

	/** Google JWKs endpoint that returns ready-to-use PEM certificates keyed by kid. */
	const CERTS_URL = 'https://www.googleapis.com/oauth2/v1/certs';

	/** Allowed `iss` claim values. */
	const ALLOWED_ISS = ['accounts.google.com', 'https://accounts.google.com'];

	/** Cache lifetime in seconds for the certificates. */
	const CACHE_TTL = 3600;

	/** @var string|false Expected `aud` claim. */
	private $clientId = false;

	/** @var array|null In-process cache: ['fetched_at'=>int, 'certs'=>array<string,string>] */
	private static $certCache = null;

	/**
	 * Static convenience wrapper.
	 *
	 * @param string $idToken
	 * @param string $clientId
	 * @return array|false
	 */
	public static function verify($idToken, $clientId) {
		$v = new self();
		$v->setClientId($clientId);
		return $v->verifyToken($idToken);
	}

	/**
	 * Sets the expected audience (`aud`) for token verification.
	 *
	 * @param string $clientId Google OAuth client_id.
	 * @return string
	 */
	public function setClientId($clientId) {
		return $this->clientId = (string)$clientId;
	}

	/**
	 * @return string|false
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * Verifies a Google ID token against the configured client_id.
	 *
	 * @param string $idToken JWT received from the GIS client (credential).
	 * @return array|false Decoded payload on success (sub, email, ...), false on failure.
	 */
	public function verifyToken($idToken) {
		if (!$idToken || !$this->clientId) {
			return false;
		}
		$parts = explode('.', $idToken);
		if (count($parts) !== 3) {
			return false;
		}
		list($h64, $p64, $s64) = $parts;

		$header  = $this->jsonDecode($this->b64uDecode($h64));
		$payload = $this->jsonDecode($this->b64uDecode($p64));
		$sig     = $this->b64uDecode($s64);
		if (!is_array($header) || !is_array($payload) || $sig === false) {
			return false;
		}
		if (($header['alg'] ?? '') !== 'RS256') {
			return false;
		}
		$kid = $header['kid'] ?? '';
		if (!$kid) {
			return false;
		}

		$pem = $this->getCertPemByKid($kid);
		if (!$pem) {
			return false;
		}
		$pubKey = openssl_pkey_get_public($pem);
		if ($pubKey === false) {
			return false;
		}
		$ok = openssl_verify($h64 . '.' . $p64, $sig, $pubKey, OPENSSL_ALGO_SHA256);
		if ($ok !== 1) {
			return false;
		}

		// Validate claims.
		$now = time();
		$iss = $payload['iss'] ?? '';
		if (!in_array($iss, self::ALLOWED_ISS, true)) {
			return false;
		}
		$aud = $payload['aud'] ?? '';
		if ($aud !== $this->clientId) {
			return false;
		}
		$exp = (int)($payload['exp'] ?? 0);
		if ($exp <= $now) {
			return false;
		}
		$nbf = (int)($payload['nbf'] ?? 0);
		if ($nbf && $nbf > $now + 60) {
			return false;
		}
		$iat = (int)($payload['iat'] ?? 0);
		if ($iat && $iat > $now + 60) {
			return false;
		}
		return $payload;
	}

	/**
	 * Returns the PEM-encoded certificate for a given key id.
	 *
	 * @param string $kid
	 * @return string|false
	 */
	protected function getCertPemByKid($kid) {
		$certs = $this->getCerts();
		if (!isset($certs[$kid])) {
			// Force refresh once in case keys rotated.
			self::$certCache = null;
			$certs = $this->getCerts(true);
		}
		return $certs[$kid] ?? false;
	}

	/**
	 * Fetches and caches Google's public certificates.
	 *
	 * @param bool $forceRefresh
	 * @return array<string,string> kid => PEM
	 */
	protected function getCerts($forceRefresh = false) {
		$now = time();
		if (!$forceRefresh && self::$certCache && ($now - self::$certCache['fetched_at']) < self::CACHE_TTL) {
			return self::$certCache['certs'];
		}
		$cacheFile = $this->getCacheFilePath();
		if (!$forceRefresh && is_readable($cacheFile)) {
			$mtime = @filemtime($cacheFile);
			if ($mtime && ($now - $mtime) < self::CACHE_TTL) {
				$raw = @file_get_contents($cacheFile);
				$certs = $this->jsonDecode($raw);
				if (is_array($certs) && $certs) {
					self::$certCache = ['fetched_at' => $mtime, 'certs' => $certs];
					return $certs;
				}
			}
		}
		$certs = $this->fetchCerts();
		if (!$certs) {
			// On fetch failure, fall back to whatever's in cache (even if stale).
			if (is_readable($cacheFile)) {
				$raw = @file_get_contents($cacheFile);
				$old = $this->jsonDecode($raw);
				if (is_array($old) && $old) {
					self::$certCache = ['fetched_at' => $now, 'certs' => $old];
					return $old;
				}
			}
			return [];
		}
		@file_put_contents($cacheFile, json_encode($certs), LOCK_EX);
		self::$certCache = ['fetched_at' => $now, 'certs' => $certs];
		return $certs;
	}

	/**
	 * Performs the HTTPS GET to the certs endpoint.
	 *
	 * @return array<string,string>|false
	 */
	protected function fetchCerts() {
		$ch = curl_init(self::CERTS_URL);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_HTTPHEADER     => ['Accept: application/json'],
		]);
		$body = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($code !== 200 || !$body) {
			return false;
		}
		$data = $this->jsonDecode($body);
		if (!is_array($data)) {
			return false;
		}
		// Sanity check: values must look like PEM certificates.
		foreach ($data as $pem) {
			if (strpos($pem, 'BEGIN CERTIFICATE') === false) {
				return false;
			}
		}
		return $data;
	}

	/**
	 * Returns a writable cache file path. Override to point to a Meralda-managed
	 * userfiles location once integration is added.
	 *
	 * @return string
	 */
	protected function getCacheFilePath() {
		$dir = sys_get_temp_dir();
		return rtrim($dir, "/\\") . DIRECTORY_SEPARATOR . 'mwmod_mw_google_certs.json';
	}

	/**
	 * Base64-URL decode.
	 *
	 * @param string $data
	 * @return string|false
	 */
	protected function b64uDecode($data) {
		$pad = strlen($data) % 4;
		if ($pad) {
			$data .= str_repeat('=', 4 - $pad);
		}
		return base64_decode(strtr($data, '-_', '+/'), true);
	}

	/**
	 * @param string|false $raw
	 * @return mixed
	 */
	protected function jsonDecode($raw) {
		if (!is_string($raw) || $raw === '') {
			return null;
		}
		return json_decode($raw, true);
	}
}
