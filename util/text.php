<?php
/**
 * Text and value utility helpers (mw modules submodule).
 * 
 * Static methods are pure and context-free (same pattern as
 * mwmod_mw_oauth_helper); instance methods may rely on the application
 * context. This is the migration target for legacy global helpers that
 * live in the core (core/util/text.php).
 */
class  mwmod_mw_util_text extends mw_apsubbaseobj{
	function __construct(){
		
	}
	function time2id($timestamp=0){
		if(!$timestamp){
			$timestamp=time();
		}
		$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
		$base = strlen($letters); 
		$baseNString = "";

		while ($timestamp > 0) {
		    // Calculate the remainder when dividing by the base
		    $remainder = $timestamp % $base;

		    // Prepend the corresponding character from the letters
		    $baseNString = $letters[$remainder] . $baseNString;

		    // Update the timestamp by integer division
		    $timestamp = (int)($timestamp / $base);
		}
		return $baseNString;
	}
	
	/**
	 * Converts a value to a number when it is numeric.
	 * 
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_get_number() (core/util/text.php).
	 * 
	 * @param mixed $val Value to convert.
	 * @return int|float|false The numeric value, or false when not numeric.
	 */
	static function get_number(mixed $val): int|float|false {
		if (is_numeric($val)) {
			return $val + 0;
		}
		return false;
	}
	
	/**
	 * Converts a value to a string when it is numeric or a string.
	 * 
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_get_string() (core/util/text.php).
	 * 
	 * @param mixed $val Value to convert.
	 * @return string|false The string value, or false when not convertible.
	 */
	static function get_string(mixed $val): string|false {
		if (is_numeric($val) || is_string($val)) {
			return (string) $val;
		}
		return false;
	}
	
	/**
	 * Validates an email address.
	 * 
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_checkemail() (core/util/text.php).
	 * 
	 * @param string $address Email address to validate.
	 * @param bool $allowEmpty When true, an empty address is considered valid.
	 * @return bool True when the address is valid (or empty and allowed).
	 */
	static function check_email(string $address, bool $allowEmpty = false): bool {
		if ($address === '') {
			return $allowEmpty;
		}
		return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
	}
	
	/**
	 * Escapes special characters so a value is safe inside a JS string literal.
	 * 
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_text_nl_js() (core/util/text.php).
	 * 
	 * @param string $str String to escape.
	 * @return string Escaped string.
	 */
	static function js_escape(string $str): string {
		return addcslashes($str, "\\\'\"&\n\r<>");
	}
	
	/**
	 * Replaces accented and special characters with ASCII equivalents.
	 * 
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_text_replace_lngchars() (core/util/text.php).
	 * 
	 * @param string $str String to process.
	 * @param string|bool $mode Operation mode:
	 *   - false: normal, for filenames.
	 *   - true: for sorting.
	 *   - 'upper': uppercase.
	 *   - 'lower': lowercase.
	 *   - 'upperindex': uppercase without accents, for directories.
	 * @param string|false $replaceNonAlphaWith Replacement char for non-alphanumeric characters; false to leave them untouched.
	 * @param bool $returnCharTable When true, returns the character table instead of the processed string.
	 * @return string|array Processed string, or the character table when requested.
	 */
	static function replace_lng_chars(
		string $str,
		string|bool $mode = false,
		string|false $replaceNonAlphaWith = false,
		bool $returnCharTable = false
	): string|array {
		// Accented character tables
		$charTable = [
			'A' => ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å'],
			'a' => ['à', 'á', 'â', 'ã', 'ä', 'å'],
			'E' => ['È', 'É', 'Ê', 'Ë'],
			'e' => ['è', 'é', 'ê', 'ë'],
			'f' => ['ƒ'],
			'I' => ['Ì', 'Í', 'Î', 'Ï'],
			'i' => ['ì', 'í', 'î', 'ï'],
			'O' => ['Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø'],
			'o' => ['ò', 'ó', 'ô', 'õ', 'ö', 'ø'],
			'S' => ['Š'],
			's' => ['š'],
			'U' => ['Ù', 'Ú', 'Û', 'Ü'],
			'u' => ['ù', 'ú', 'û', 'ü'],
			'y' => ['ý', 'ÿ'],
			'Y' => ['Ý', 'Ÿ'],
			'Z' => ['Ž'],
			'z' => ['ž']
		];
		
		$vowels = ['a', 'e', 'i', 'o', 'u'];
		$otherAccented = ['y', 'z', 'S'];
		
		$specialPairs = [
			'AE' => ['Æ'],
			'ae' => ['æ'],
			'OE' => ['Œ'],
			'oe' => ['œ'],
			'ss' => ['ß']
		];
		
		$specialToUpper = [
			'Æ' => ['æ'],
			'Œ' => ['œ'],
			'Ñ' => ['ñ'],
			'Ð' => ['ð'],
			'Ç' => ['ç']
		];
		
		$specialToLower = [
			'æ' => ['Æ'],
			'œ' => ['Œ'],
			'ñ' => ['Ñ'],
			'ð' => ['Ð'],
			'ç' => ['Ç']
		];
		
		// Apply transformations according to mode
		switch ($mode) {
			case 'upper':
				$vowelsExt = array_merge($vowels, $otherAccented);
				foreach ($vowelsExt as $vowel) {
					if ($lower = $charTable[$vowel] ?? null) {
						if ($upper = $charTable[strtoupper($vowel)] ?? null) {
							foreach ($lower as $i => $lowerChar) {
								$upperChar = $upper[$i];
								$charTable[$upperChar] = [$lowerChar];
							}
						}
						unset($charTable[$vowel], $charTable[strtoupper($vowel)]);
					}
				}
				$charTable = array_merge($charTable, $specialToUpper);
				$charTable['SS'] = ['ß'];
				break;
				
			case 'lower':
				$vowelsExt = array_merge($vowels, $otherAccented);
				foreach ($vowelsExt as $vowel) {
					if ($lower = $charTable[$vowel] ?? null) {
						if ($upper = $charTable[strtoupper($vowel)] ?? null) {
							foreach ($lower as $i => $lowerChar) {
								$upperChar = $upper[$i];
								$charTable[$lowerChar] = [$upperChar];
							}
						}
						unset($charTable[$vowel], $charTable[strtoupper($vowel)]);
					}
				}
				$charTable = array_merge($charTable, $specialToLower);
				break;
				
			case 'upperindex':
				foreach ($vowels as $vowel) {
					$upper = strtoupper($vowel);
					$charTable[$upper] = array_merge($charTable[$upper] ?? [], $charTable[$vowel] ?? []);
					unset($charTable[$vowel]);
				}
				$charTable = array_merge($charTable, $specialToUpper);
				break;
				
			default:
				$charTable = array_merge($charTable, $specialPairs);
				if ($mode === true) {
					// Sorting mode
					$charTable['Dz'] = ['Ð'];
					$charTable['dz'] = ['ð'];
					$charTable['Nz'] = ['Ñ'];
					$charTable['nz'] = ['ñ'];
					$charTable['Zz'] = ['Ç'];
					$charTable['zz'] = ['ç'];
				} else {
					// Normal mode
					$charTable['D'] = ['Ð'];
					$charTable['d'] = ['ð'];
					$charTable['N'] = ['Ñ'];
					$charTable['n'] = ['ñ'];
					$charTable['c'] = ['Ç', 'ç'];
				}
				break;
		}
		
		if ($returnCharTable) {
			return $charTable;
		}
		
		// Perform replacements
		foreach ($charTable as $replacement => $originals) {
			foreach ($originals as $original) {
				$str = str_replace($original, $replacement, $str);
			}
		}
		
		// Transform to uppercase/lowercase according to mode
		if ($mode === 'upperindex' || $mode === 'upper') {
			$str = strtoupper($str);
		} elseif ($mode === 'lower') {
			$str = strtolower($str);
		}
		
		// Replace non-alphabetic characters if specified
		if ($replaceNonAlphaWith !== false) {
			$chars = mb_str_split($str);
			foreach ($chars as $i => $char) {
				if (!ctype_alpha($char) && !isset($charTable[$char])) {
					$chars[$i] = $replaceNonAlphaWith;
				}
			}
			$str = implode('', $chars);
		}
		
		return $str;
	}
	
}
?>