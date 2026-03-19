<?php
/**
 * Represents a single form submission stored in the form's own table.
 */
class mwmod_mw_formsubmit_item extends mwmod_mw_manager_item {

	function __construct($tblitem, $man) {
		$this->init($tblitem, $man);
	}

	function getIP() {
		return $this->get_data("ip_address");
	}

	function getSubmittedAt() {
		return $this->get_data("submitted_at");
	}

	/** Returns status integer: 0 = new, 1 = read, 2 = processed. */
	function getStatus() {
		return (int)$this->get_data("status");
	}

	/**
	 * Returns the submitted form fields as an associative array.
	 *
	 * @return array
	 */
	function getFormData() {
		$json = $this->get_data("data_json");
		if (!$json) {
			return [];
		}
		return json_decode($json, true) ?: [];
	}

	/** Marks the submission as read (status = 1). */
	function markAsRead() {
		$this->do_save_data(["status" => 1]);
	}

	/** Marks the submission as processed (status = 2). */
	function markAsProcessed() {
		$this->do_save_data(["status" => 2]);
	}
}
?>
