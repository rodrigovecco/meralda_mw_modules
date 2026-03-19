<?php
/**
 * Generic form submission manager.
 *
 * Extend this class for each form, declaring the DB table name and
 * optionally overriding the rate-limit thresholds:
 *
 *   class mwap_myproject_contact_man extends mwmod_mw_formsubmit_man {
 *       function getTableName()               { return "contact_submissions"; }
 *       function getMaxSubmissionsPerWindow() { return 3; }
 *       function getWindowMinutes()           { return 30; }
 *   }
 *
 * Create the table using the template in docs/db/formsubmit.sql.
 */
class mwmod_mw_formsubmit_man extends mwmod_mw_formsubmit_manbase {

	function getTableName() {
		return false;
	}
}
?>
