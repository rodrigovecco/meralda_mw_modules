<?php
/**
 * Abstract base manager for generic form submissions with IP-based rate limiting.
 *
 * Extends mwmod_mw_manager_man directly — it IS the DB manager for the form's
 * table, AND it is the /get endpoint handler registered as a submanager on the
 * application.
 *
 * Routing:
 *   POST /get/[mancod]/submit
 *        ↓
 *   $ap->get_submanager(mancod)->exec_getcmd_submit($params, $filename)
 *
 * The manager handles user-validation bypass itself via
 * checkGetCmdOmitValidateUser(), so no UI class is involved at all.
 *
 * Minimal usage:
 *
 *   // 1. Create a manager for the form
 *   class mwap_myproject_contact_man extends mwmod_mw_formsubmit_man {
 *       function getTableName()               { return "contact_submissions"; }
 *       function getMaxSubmissionsPerWindow() { return 3; }
 *       function getWindowMinutes()           { return 30; }
 *       // Optional: whitelist POST fields
 *       function getAllowedFields()           { return ["name","email","message"]; }
 *   }
 *
 *   // 2. Register as a submanager on the app (e.g. in create_submanager_contact())
 *   function create_submanager_contact() {
 *       return new mwap_myproject_contact_man($this);
 *   }
 *
 *   // 3. POST to /get/contact/submit with body data[name]=...&data[email]=... — returns JSON.
 */
abstract class mwmod_mw_formsubmit_manbase extends mwmod_mw_manager_man {

	/** @var mwmod_mw_util_itemsbycod */
	private $statusList;

	function __construct($ap) {
		$this->init("formsubmit", $ap, $this->getTableName());
	}

	// ---- Required override ------------------------------------------------

	/** Name of the DB table that stores submissions for this form. */
	abstract function getTableName();

	// ---- Configurable limits (override in subclass) -----------------------

	/** Maximum submissions allowed from one IP within the time window. */
	function getMaxSubmissionsPerWindow() {
		return 5;
	}

	/** Length of the sliding time window in minutes. */
	function getWindowMinutes() {
		return 60;
	}

	// ---- Status list (lazy-loaded) ----------------------------------------

	/**
	 * Populates the statusList. Override to add/rename statuses.
	 * Status codes match the `status` column values in the table.
	 */
	function statusListCreate($list) {
		$list->add_item(new mwmod_mw_util_itemsbycod_item(1, $this->lng_get_msg_txt("status_new",       "New")));
		$list->add_item(new mwmod_mw_util_itemsbycod_item(2, $this->lng_get_msg_txt("status_read",      "Read")));
		$list->add_item(new mwmod_mw_util_itemsbycod_item(3, $this->lng_get_msg_txt("status_processed", "Processed")));
	}

	/** @return mwmod_mw_util_itemsbycod */
	final function __get_priv_statusList() {
		if (!isset($this->statusList)) {
			$this->statusList = new mwmod_mw_util_itemsbycod();
			$this->statusListCreate($this->statusList);
		}
		return $this->statusList;
	}

	// ---- Item factory -----------------------------------------------------

	function create_item($tblitem) {
		return new mwmod_mw_formsubmit_item($tblitem, $this);
	}

	// ---- IP helper --------------------------------------------------------

	function getCurrentIP() {
		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}

	// ---- Core logic -------------------------------------------------------

	/**
	 * Returns true if the current IP has exceeded the submission limit
	 * within the configured time window.
	 */
	function isRateLimited() {
		$ip = $this->getCurrentIP();
		return $this->countRecentByIP($ip, $this->getWindowMinutes()) >= $this->getMaxSubmissionsPerWindow();
	}

	/**
	 * Submits a form. Returns the saved item, or false if rate-limited / invalid.
	 *
	 * @param array $data Associative array of form field values.
	 * @return mwmod_mw_formsubmit_item|false
	 */
	function submit($data) {
		if (!is_array($data)) {
			return false;
		}
		if ($this->isRateLimited()) {
			return false;
		}
		$item = $this->saveSubmission($this->getCurrentIP(), $data);
		if ($item) {
			$this->afterSubmit($item);
		}
		return $item;
	}

	/**
	 * Inserts a new submission row.
	 *
	 * @param string $ip
	 * @param array  $data  Form field values (JSON-encoded on save).
	 * @return mwmod_mw_formsubmit_item|false
	 */
	function saveSubmission($ip, $data) {
		$nd = [
			"ip_address"   => $ip,
			"submitted_at" => date("Y-m-d H:i:s"),
			"data_json"    => json_encode($data, JSON_UNESCAPED_UNICODE),
			"status"       => 1,
		];
		return $this->insert_item($nd);
	}

	/**
	 * Counts submissions from $ip within the last $windowMinutes.
	 *
	 * @param string $ip
	 * @param int    $windowMinutes
	 * @return int
	 */
	function countRecentByIP($ip, $windowMinutes) {
		if (!$tblman = $this->get_tblman()) {
			return 0;
		}
		$since = date("Y-m-d H:i:s", strtotime("-" . (int)$windowMinutes . " minutes"));
		$query = $tblman->new_query();
		$query->where->add_where_crit("ip_address", $ip);
		$query->where->add_time_cond("submitted_at", $since, ">=");
		if (!$items = $this->get_items_by_query($query)) {
			return 0;
		}
		return count($items);
	}

	/**
	 * Returns recent submissions, newest first.
	 *
	 * @param int $limit
	 * @return mwmod_mw_formsubmit_item[]|false
	 */
	function getRecentItems($limit = 100) {
		if (!$tblman = $this->get_tblman()) {
			return false;
		}
		$query = $tblman->new_query();
		$query->order->add_order("submitted_at", "DESC");
		$query->limit->set_limit($limit);
		return $this->get_items_by_query($query);
	}

	// ---- Post-submit hook -------------------------------------------------

	/**
	 * Called after a submission is saved successfully.
	 * Override to add custom post-submit logic.
	 */
	function afterSubmit($item) {
		$this->sendNotificationEmail($item);
	}

	// ---- Email notification (configured via cfg.ini) ----------------------

	/**
	 * Returns a sanitized cfg key base derived from the table name.
	 * e.g. "lkc_contact" → "formsubmit_lkc_contact"
	 */
	function getCfgBaseKey() {
		$safe = preg_replace('/[^a-zA-Z0-9]/', '_', $this->getTableName());
		return "formsubmit_" . $safe;
	}

	/**
	 * Returns true when email notifications are enabled in cfg.ini.
	 * cfg key: formsubmit_[tablename]_mail_enabled
	 */
	function isEmailNotificationEnabled() {
		if (!$cfg = $this->mainap->cfg) {
			return false;
		}
		return $cfg->get_value_boolean($this->getCfgBaseKey() . "_mail_enabled");
	}

	/**
	 * Returns the destination email address from cfg.ini, or false if not set.
	 * cfg key: formsubmit_[tablename]_mail_to
	 */
	function getNotificationEmailTo() {
		if (!$cfg = $this->mainap->cfg) {
			return false;
		}
		$val = $cfg->get_value($this->getCfgBaseKey() . "_mail_to");
		if ($val && mw_checkemail(trim($val))) {
			return trim($val);
		}
		return false;
	}

	/**
	 * Builds a plain-text email body from the submission item.
	 * Uses getAllowedFields() when available so individual-column managers work
	 * correctly; falls back to data_json for legacy managers.
	 */
	function getNotificationEmailBody($item) {
		$lines = [];
		$lines[] = $this->lng_get_msg_txt("mail_body_header", "New form submission");
		$lines[] = "";
		$lines[] = "IP: " . $item->getIP();
		$lines[] = $this->lng_get_msg_txt("submitted_at", "Submitted") . ": " . $item->getSubmittedAt();
		$lines[] = "";
		$fields = $this->getAllowedFields();
		if ($fields) {
			foreach ($fields as $field) {
				$val = $item->get_data($field);
				$lines[] = $field . ": " . ($val !== null ? $val : "");
			}
		} else {
			foreach ($item->getFormData() as $k => $v) {
				$lines[] = $k . ": " . $v;
			}
		}
		return implode("\n", $lines);
	}

	/**
	 * Sends an email notification for a new submission.
	 * Does nothing if email notifications are disabled or not configured.
	 */
	function sendNotificationEmail($item) {
		if (!$this->isEmailNotificationEnabled()) {
			return false;
		}
		if (!$to = $this->getNotificationEmailTo()) {
			return false;
		}
		if (!$sysmail = $this->mainap->get_submanager("sysmail")) {
			return false;
		}
		if (!$phpmailer_man = $sysmail->get_phpmailer_man()) {
			return false;
		}
		if (!$mailer = $phpmailer_man->preparePHPMailer()) {
			return false;
		}
		$mailer->CharSet = "utf-8";
		$mailer->addAddress($to);
		$mailer->Subject = $this->lng_get_msg_txt("new_submission", "Nueva respuesta") . " [" . $this->getTableName() . "]";
		$mailer->Body    = $this->getNotificationEmailBody($item);
		$mailer->isHTML(false);
		return $mailer->send();
	}

	// ---- /get endpoint ---------------------------------------------------

	/** Required by the app router (apabs.php) to enable /get URL commands. */
	function __accepts_exec_cmd_by_url() {
		return true;
	}

	/** Hardcoded: submit is always public (no login required). */
	function checkGetCmdOmitValidateUser($cmdcod, $params, $filename) {
		return true;
	}

	/**
	 * Override to control whether this form accepts submissions at all.
	 * Return false to silently reject (e.g. form is closed, honeypot failed).
	 */
	function isSubmitAllowed() {
		return false; // Default: reject all submissions. Override in subclass.
	}

	/**
	 * Handles POST /get/[mancod]/submit
	 *
	 * Response on success:  {"ok":true,  "id":42}
	 * Response on limit:    {"ok":false, "rate_limited":true, "msg":"..."}
	 * Response on error:    {"ok":false, "msg":"..."}
	 */
	function exec_getcmd_submit($params = array(), $filename = false) {
		ob_end_clean();

		if (!$this->isSubmitAllowed()) {
			return $this->_json_output([
				"ok"  => false,
				"msg" => $this->lng_get_msg_txt("not_allowed", "No permitido"),
			]);
		}

		if ($this->isRateLimited()) {
			return $this->_json_output([
				"ok"           => false,
				"rate_limited" => true,
				"msg"          => $this->lng_get_msg_txt("rate_limited", "Demasiados envíos. Por favor intente más tarde."),
			]);
		}

		if (!$item = $this->submit($this->_getPostData())) {
			return $this->_json_output([
				"ok"  => false,
				"msg" => $this->lng_get_msg_txt("submit_failed", "Error al enviar. Por favor intente nuevamente."),
			]);
		}

		return $this->_json_output([
			"ok" => true,
			"id" => $item->get_id(),
		]);
	}

	// ---- Override in subclass to whitelist fields ------------------------

	/**
	 * Return a list of allowed field names inside $_REQUEST["data"],
	 * or false to accept all keys.
	 *
	 * @return string[]|false
	 */
	function getAllowedFields() {
		return false;
	}

	// ---- Internal helpers ------------------------------------------------

	/**
	 * Reads $_REQUEST["data"] (expects data[field]=value from the client).
	 * Filters keys through getAllowedFields() when defined.
	 */
	protected function _getPostData() {
		$raw = $_REQUEST["data"] ?? [];
		if (!is_array($raw)) {
			return [];
		}
		$allowed = $this->getAllowedFields();
		$data = [];
		foreach ($raw as $k => $v) {
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $k)) {
				continue;
			}
			if ($allowed !== false && !in_array($k, $allowed, true)) {
				continue;
			}
			$data[$k] = $v;
		}
		return $data;
	}

	protected function _json_output($data) {
		header("Content-Type: application/json; charset=UTF-8");
		echo json_encode($data);
	}
}
?>
