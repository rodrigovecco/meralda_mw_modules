<?php
/**
 * Random value utilities (mw modules submodule).
 *
 * Thin, static wrappers over PHP's CSPRNG (random_int) so framework code
 * never falls back to the predictable rand()/mt_rand() functions. The
 * generated strings are safe for passwords, tokens and cookie values.
 *
 * This is the migration target for the legacy core helpers mw_randPass()
 * and randPass() (core/util/text.php).
 */
class mwmod_mw_util_random {

	/** Digits + uppercase + lowercase (62 characters). */
	const ALNUM = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	/** Lowercase letters only. */
	const LOWER = 'abcdefghijklmnopqrstuvwxyz';

	/** Uppercase letters only. */
	const UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/** Digits only. */
	const DIGITS = '0123456789';

	/**
	 * Generates a cryptographically secure random string.
	 *
	 * Each character is drawn uniformly with random_int(), so there is no
	 * modulo bias over the alphabet and the output is not predictable.
	 *
	 * @param int $length Number of characters to generate.
	 * @param string $alphabet Source character set. Defaults to ALNUM.
	 * @return string The generated string, or '' when $length < 1.
	 */
	static function string(int $length, string $alphabet = self::ALNUM): string {
		$length = $length + 0;
		if ($length < 1 || $alphabet === '') {
			return '';
		}

		$max = strlen($alphabet) - 1;
		$out = '';

		for ($i = 0; $i < $length; $i++) {
			$out .= $alphabet[random_int(0, $max)];
		}

		return $out;
	}

	/**
	 * Generates an opaque URL-safe token from raw entropy.
	 *
	 * base64url of N random bytes, with the padding stripped: no '+', '/' or '='
	 * so the value survives a query string, a JSON body and a CSV cell
	 * untouched. 32 bytes (the default) give 256 bits of entropy in 43 chars.
	 *
	 * Prefer this over string() for credentials: it carries a known amount of
	 * entropy per character, while string() with a reduced alphabet does not.
	 *
	 * @param int $bytes Entropy size. Defaults to 32 (256 bits).
	 * @return string
	 */
	static function token(int $bytes = 32): string {
		$bytes = $bytes + 0;
		if ($bytes < 1) {
			return '';
		}

		return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
	}

	/**
	 * Generates a random password that always mixes the three character
	 * classes required by the default password policy (lowercase, uppercase
	 * and digits), so it passes mwmod_mw_users_passpolicy::check_new_pass().
	 *
	 * The alphabet is alphanumeric only, so the result never contains the
	 * characters rejected by the policy (spaces and quotes).
	 *
	 * @param int $length Desired password length. Defaults to 10.
	 * @return string The generated password.
	 */
	static function password(int $length = 10): string {
		$length = $length + 0;
		if ($length < 3) {
			return self::string($length);
		}

		$chars = self::string(1, self::LOWER)
			. self::string(1, self::UPPER)
			. self::string(1, self::DIGITS)
			. self::string($length - 3);

		return self::shuffle($chars);
	}

	/**
	 * Cryptographically secure shuffle of a string.
	 *
	 * Fisher-Yates using random_int(). PHP's str_shuffle() is intentionally
	 * not used because it relies on the non-cryptographic Mersenne Twister.
	 *
	 * @param string $str String to shuffle.
	 * @return string The shuffled string.
	 */
	static function shuffle(string $str): string {
		$chars = str_split($str);
		$last = count($chars) - 1;

		for ($i = $last; $i > 0; $i--) {
			$j = random_int(0, $i);
			$tmp = $chars[$i];
			$chars[$i] = $chars[$j];
			$chars[$j] = $tmp;
		}

		return implode('', $chars);
	}
}
?>
