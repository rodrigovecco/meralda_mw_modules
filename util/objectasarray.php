<?php
/**
 * Array-like object base class (mw modules submodule).
 *
 * Canonical, maintained base class for objects that expose their properties
 * through dot-notation array access. Module code must extend THIS class, not
 * the deprecated core ancestor mw_object_as_array.
 *
 * It is fully standalone: it no longer extends the core class, and its logic
 * (dot access, array/string conversion) lives in mwmod_mw_util_array. Legacy
 * subclasses of the core class keep working because
 * mwmod_mw_util_array::is_obj_array_allowed() recognizes both class families.
 *
 * @see mwmod_mw_util_array::is_obj_array_allowed()
 * @see mw_object_as_array Deprecated legacy ancestor kept for back-compat.
 */
abstract class mwmod_mw_util_objectasarray {

	/** @var mixed Object properties storage */
	public $props;

	/**
	 * Determines if this object allows array-like access.
	 *
	 * Override in subclasses to enable array operations.
	 *
	 * @return bool True if array access is allowed.
	 */
	function __mw_array_allow_use_this_object() {
		return false;
	}

	/**
	 * Determines if this object allows write operations.
	 *
	 * @return bool True if write access is allowed.
	 */
	function __mw_array_allow_write() {
		return $this->__mw_array_allow_use_this_object();
	}

	/**
	 * Sets all properties.
	 *
	 * @param mixed $val Properties value.
	 * @return void
	 */
	function set_props($val) {
		$this->props = $val;
	}

	/**
	 * Gets all properties.
	 *
	 * @return mixed Properties value or null.
	 */
	function get_props() {
		return $this->props ?? null;
	}

	/**
	 * Gets a property using dot notation.
	 *
	 * @param string|false $key Dot-separated key path.
	 * @return mixed Value at the specified path.
	 */
	function get_prop_from_key_dot($key = false) {
		$a = $this->get_props();
		if (!$key) {
			return $a;
		}
		return mwmod_mw_util_array::get_sub_key($a, $key);
	}

	/**
	 * Gets properties as array or string.
	 *
	 * @return mixed Properties converted to array or string.
	 */
	function get_props_as_array_or_str() {
		$r = $this->get_props();
		return mwmod_mw_util_array::get_obj_as_array_or_str($r);
	}

	/**
	 * String representation of the object.
	 *
	 * @return string Class name.
	 */
	function __toString(): string {
		return get_class($this);
	}

	/**
	 * Sets a property via mwmod_mw_util_array::set_sub_key if write is allowed.
	 *
	 * @param string $key Dot-separated key path.
	 * @param mixed $val Value to set.
	 * @return mixed Result of set operation or false.
	 */
	function set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key($key, $val) {
		if (!$this->__mw_array_allow_write()) {
			return false;
		}
		return $this->set_prop_from_key_dot($key, $val);
	}

	/**
	 * Sets all properties via mwmod_mw_util_array::set_sub_key if write is allowed.
	 *
	 * @param mixed $val Value to set.
	 * @return mixed Result of set operation or false.
	 */
	function set_all_prop_from_mw_array_set_sub_key($val) {
		if (!$this->__mw_array_allow_write()) {
			return false;
		}
		return $this->set_props($val);
	}

	/**
	 * Sets a property using dot notation.
	 *
	 * @param string $key Dot-separated key path.
	 * @param mixed $val Value to set.
	 * @return bool True on success.
	 */
	function set_prop_from_key_dot($key, $val) {
		if (!$key) {
			return false;
		}

		if (!isset($this->props) || !is_array($this->props)) {
			$this->props = [];
		}

		mwmod_mw_util_array::set_sub_key($key, $val, $this->props);
		return true;
	}
}
?>
