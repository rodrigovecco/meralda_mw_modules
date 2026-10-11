<?php
/**
 * Array utility helpers (mw modules submodule).
 *
 * Static, context-free helpers for array manipulation. This is the
 * migration target for the legacy global helpers that live in the core
 * (core/util/array.php) so they can be maintained and updated here.
 */
class mwmod_mw_util_array {

	/**
	 * Recursively merges defaults into data where data is null or missing.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array_bydefault() (core/util/array.php).
	 *
	 * @param mixed $data Source data array or scalar.
	 * @param mixed $defaults Default values to apply.
	 * @param bool $emptyStringAsNull When true, empty strings count as null.
	 * @return mixed Merged data with defaults applied.
	 */
	static function by_default($data, $defaults, bool $emptyStringAsNull = false) {
		if (is_object($data)) {
			return $data;
		} elseif (is_object($defaults)) {
			return $defaults;
		}

		if ($emptyStringAsNull) {
			if (is_string($data) && strlen($data) <= 0) {
				$data = null;
			}
		}

		if (is_array($defaults)) {
			if (is_array($data) || is_null($data)) {
				foreach ($defaults as $key => $val) {
					$data[$key] = self::by_default($data[$key] ?? null, $val, $emptyStringAsNull);
				}
			}
		} elseif (!is_null($defaults) && is_null($data)) {
			$data = $defaults;
		}

		return $data;
	}

	/**
	 * Converts a nested array into a URL query string.
	 *
	 * Nested arrays use bracket notation (a[b]=c). Null values are omitted.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array2urlquery() (core/util/array.php).
	 *
	 * @param array $data Array to convert.
	 * @return string|false Query string, or false when there is nothing to encode.
	 */
	static function to_url_query($data) {
		if (!is_array($data)) {
			return false;
		}

		$args = [];
		self::collect_url_args($args, $data, "");

		if (!is_array($args) || !sizeof($args)) {
			return false;
		}

		$pairs = [];
		foreach ($args as $k => $v) {
			if (!is_null($v)) {
				$pairs[] = $k . "=" . urlencode((string) $v);
			}
		}

		return implode("&", $pairs);
	}

	/**
	 * Recursively flattens a nested array into path => value pairs.
	 *
	 * Internal helper for to_url_query().
	 *
	 * @param array &$args Reference to the accumulated arguments.
	 * @param mixed $data Current data to process.
	 * @param string|false $prefix Current key path.
	 * @return array The accumulated arguments.
	 */
	private static function collect_url_args(&$args, $data, $prefix = false) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$name = $prefix ? $prefix . "[" . $k . "]" : $k;
				self::collect_url_args($args, $v, $name);
			}
		} else {
			if ($prefix) {
				$args[$prefix] = $data;
			}
		}

		return $args;
	}

	/**
	 * Checks whether a value is an allowed array-like object.
	 *
	 * Recognizes instances of the canonical base class
	 * (mwmod_mw_util_objectasarray) as well as legacy subclasses of the
	 * core class (mw_object_as_array), whose array access must be enabled.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array_is_obj_mw_array_allowed() (core/util/array.php).
	 *
	 * @param mixed $obj Value to check.
	 * @return bool True when the object exposes array-like access.
	 */
	static function is_obj_array_allowed($obj): bool {
		if (!$obj || !is_object($obj)) {
			return false;
		}

		$allowed = ($obj instanceof mwmod_mw_util_objectasarray)
			|| ($obj instanceof mw_object_as_array);

		if (!$allowed) {
			return false;
		}

		return (bool) $obj->__mw_array_allow_use_this_object();
	}

	/**
	 * Gets a value from a nested array or array-like object using dot notation.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array_get_sub_key() (core/util/array.php).
	 *
	 * @param array|object $data Source array or array-like object.
	 * @param string $key Dot-separated key path (e.g. "parent.child.value").
	 * @return mixed Value at the path, or false when it is not reachable.
	 */
	static function get_sub_key($data, $key = "") {
		if (self::is_obj_array_allowed($data)) {
			return $data->get_prop_from_key_dot($key);
		}

		if (!$key) {
			return $data;
		}

		$val = $data;
		$keys = [];
		$keysLeft = [];

		foreach (explode(".", $key) as $k) {
			if ($k) {
				$keys[] = $k;
				$keysLeft[] = $k;
			}
		}

		if (sizeof($keys) == 0) {
			return $val;
		}

		foreach ($keys as $k) {
			if (self::is_obj_array_allowed($val)) {
				return $val->get_prop_from_key_dot(implode(".", $keysLeft));
			}

			if (!is_array($val)) {
				return false;
			}

			array_shift($keysLeft);
			$val = isset($val[$k]) ? $val[$k] : null;
		}

		return $val;
	}

	/**
	 * Sets a value in a nested array using dot notation.
	 *
	 * Creates intermediate arrays as needed and supports array-like objects.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array_set_sub_key() (core/util/array.php).
	 *
	 * @param string|int $key Dot-separated key path.
	 * @param mixed $val Value to set.
	 * @param array|object &$data Reference to the target array or object.
	 * @param bool $replaceNonArray When true, non-array intermediates become arrays.
	 * @return array|false The modified array, or false on failure.
	 */
	static function set_sub_key($key, $val, &$data, $replaceNonArray = true) {
		if (!$key) {
			return false;
		}

		if (self::is_obj_array_allowed($data)) {
			return $data->set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key($key, $val);
		}

		if (!is_array($data)) {
			$data = [];
		}

		if (!is_string($key) && !is_numeric($key)) {
			return false;
		}

		$keys = [];
		$keysLeft = [];

		foreach (explode(".", $key) as $k) {
			if ($k) {
				$keys[] = $k;
				$keysLeft[] = $k;
			}
		}

		if (sizeof($keys) == 0) {
			return false;
		}

		$lastKey = array_pop($keys);
		$cursor = &$data;

		if (sizeof($keys) > 0) {
			foreach ($keys as $k) {
				array_shift($keysLeft);

				if (!isset($cursor[$k])) {
					$cursor[$k] = [];
				}

				if (self::is_obj_array_allowed($cursor[$k])) {
					return $cursor[$k]->set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key(implode(".", $keysLeft), $val);
				}

				if (!is_array($cursor[$k])) {
					if ($replaceNonArray) {
						$cursor[$k] = [];
					} else {
						return false;
					}
				}

				$cursorChild = &$cursor[$k];
				$cursor = &$cursorChild;
			}
		}

		if (isset($cursor[$lastKey])) {
			if (self::is_obj_array_allowed($cursor[$lastKey])) {
				return $cursor[$lastKey]->set_all_prop_from_mw_array_set_sub_key($val);
			}
		}

		$cursor[$lastKey] = $val;
		return $data;
	}

	/**
	 * Converts an object to its array or string representation.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array_get_obj_as_array_or_str() (core/util/array.php).
	 *
	 * @param mixed $data Value to convert.
	 * @return mixed Array, string representation, or the original value.
	 */
	static function get_obj_as_array_or_str($data) {
		if (is_bool($data)) {
			return $data ? "true" : "false";
		}

		if (is_object($data)) {
			if (self::is_obj_array_allowed($data)) {
				$data = $data->get_props_as_array_or_str();
			} elseif (method_exists($data, '__toString')) {
				$data = $data . "";
			} else {
				$data = "";
			}
		}

		if (is_bool($data)) {
			$data = $data ? "true" : "false";
		}

		return $data;
	}

	/**
	 * Renders a nested array or array-like object as an HTML unordered list.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array2list() (core/util/array.php).
	 *
	 * @param mixed $data Array, object or scalar to render.
	 * @param array|null $params Rendering options:
	 *        - 'new' (bool): wrap the output in a new <ul>.
	 *        - 'name' (string): label for this level.
	 *        - 'fnc_if_array' (string): callable name for arrays.
	 *        - 'fnc_if_value' (string): callable name for scalar values.
	 * @return string HTML list markup.
	 */
	static function to_list($data, $params = null): string {
		$r = "";

		if (!is_array($params)) {
			$params = [];
		}

		if (!empty($params["new"])) {
			$r .= "<ul>";
		}

		if (is_object($data)) {
			if (self::is_obj_array_allowed($data)) {
				$data = $data->get_props_as_array_or_str();
			} elseif (method_exists($data, '__toString')) {
				$data = $data . " [" . get_class($data) . "]";
			} else {
				$data = "[" . get_class($data) . "]";
			}
		}

		if (is_bool($data)) {
			$data = $data ? "true" : "false";
		}

		$fncIfArray = false;
		if (!empty($params["fnc_if_array"]) && is_string($params["fnc_if_array"])) {
			if (function_exists($params["fnc_if_array"])) {
				$fncIfArray = $params["fnc_if_array"];
			}
		}

		$fncIfValue = false;
		if (!empty($params["fnc_if_value"]) && is_string($params["fnc_if_value"])) {
			if (function_exists($params["fnc_if_value"])) {
				$fncIfValue = $params["fnc_if_value"];
			}
		}

		if (is_array($data)) {
			if ($fncIfArray) {
				$r .= call_user_func($fncIfArray, $data, $params);
			} else {
				$r .= "<li><div >";
				$r .= $params["name"] ?? "";
				$r .= "</div>";
				$r .= "<ul>";

				foreach ($data as $key => $val) {
					$childParams = $params;
					$childParams["new"] = 0;
					$childParams["name"] = $key;
					$r .= self::to_list($val, $childParams);
				}

				$r .= "</ul>";
				$r .= "</li>";
			}
		} else {
			if ($fncIfValue) {
				$r .= call_user_func($fncIfValue, $data, $params);
			} else {
				$r .= "<li>";
				$r .= ($params["name"] ?? "") . " == > " . $data;
				$r .= "</li>";
			}
		}

		if (!empty($params["new"])) {
			$r .= "</ul>";
		}

		return $r;
	}

	/**
	 * Renders a nested array or object as an HTML list, with an optional echo.
	 *
	 * Static, context-free helper. Canonical replacement for the deprecated
	 * core helper mw_array2list_echo() (core/util/array.php).
	 *
	 * @param mixed $data Array or data to render.
	 * @param string $title Label for the list.
	 * @param bool $echo When true, echoes the markup in addition to returning it.
	 * @return string HTML markup.
	 */
	static function to_list_echo($data, $title = "DATA", $echo = true) {
		$params = [];
		$params["new"] = true;
		$params["name"] = $title;

		$html = "<div align='left'>";
		$html .= self::to_list($data, $params);
		$html .= "</div>";

		if ($echo) {
			echo $html;
		}

		return $html;
	}
}
?>
