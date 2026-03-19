<?php
/**
 * Abstract DevExtreme admin grid for a formsubmit manager.
 *
 * Shows all submissions in a read-only table (no insert, status editable).
 * Reads email notification settings from cfg.ini via the form manager.
 *
 * Usage — one concrete class per form:
 *
 *   class mwap_lkcr_contact_adminui extends mwmod_mw_formsubmit_ui_adminui {
 *       function getFormMan() {
 *           return $this->mainap->get_submanager("contact");
 *       }
 *   }
 *
 *   // In uiadmin/main.php:
 *   function create_subinterface_contact() {
 *       return new mwap_lkcr_contact_adminui("contact", $this);
 *   }
 *
 * cfg.ini keys (set per form via table name):
 *   formsubmit_[tablename]_mail_enabled = 1
 *   formsubmit_[tablename]_mail_to      = admin@example.com
 */
abstract class mwmod_mw_formsubmit_ui_adminui extends mwmod_mw_ui_base_dxtbladmin {

	/** @return mwmod_mw_formsubmit_manbase */
	abstract function getFormMan();

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("submissions", "Envíos"));
		$this->js_ui_class_name = "mw_ui_grid_remote";
		$this->editingMode      = "cell";
		$this->defPageSize      = 50;
	}

	function load_items_man() {
		return $this->getFormMan();
	}

	// ---- Permissions -------------------------------------------------------

	function allowInsert() {
		return false;
	}

	function allowDelete() {
		return $this->allow_admin();
	}

	function allowUpdate() {
		return $this->allow_admin();
	}

	// ---- Columns -----------------------------------------------------------

	function add_cols($datagrid) {
		$col = $datagrid->add_column_number("id", "ID");
		$col->js_data->set_prop("width", 60);
		$col->js_data->set_prop("allowEditing", false);

		$col = $datagrid->add_column_date("submitted_at", $this->lng_get_msg_txt("submitted_at", "Enviado"));
		$col->js_data->set_prop("dataType", "datetime");
		$col->js_data->set_prop("allowEditing", false);
		$col->js_data->set_prop("width", 160);

		$col = $datagrid->add_column_string("ip_address", $this->lng_get_msg_txt("ip_address", "IP"));
		$col->js_data->set_prop("allowEditing", false);
		$col->js_data->set_prop("width", 140);

		$col = $datagrid->add_column_number("status", $this->lng_get_msg_txt("status", "Estado"));
		$col->js_data->set_prop("width", 130);
		if ($man = $this->getFormMan()) {
			$lu = $col->set_lookup("cod", "name");
			if ($items = $man->statusList->get_items()) {
				foreach ($items as $item) {
					$entry = $lu->add_data_obj();
					$entry->set_prop("cod",  $item->get_cod());
					$entry->set_prop("name", $item->get_name());
				}
			}
		}

		$col = $datagrid->add_column_string("data_json", $this->lng_get_msg_txt("data", "Datos"));
		$col->js_data->set_prop("allowEditing", false);
	}

	// ---- Save (status only) -----------------------------------------------

	function saveItem($item, $nd) {
		if (isset($nd["status"])) {
			$item->do_save_data(["status" => (int)$nd["status"]]);
		}
	}

	// ---- Top info panel ----------------------------------------------------

	function getTopHtml($container) {
		/*
		$man = $this->getFormMan();
		if (!$man) {
			return;
		}
		$enabled = $man->isEmailNotificationEnabled()
			? $this->lng_get_msg_txt("yes", "Sí")
			: $this->lng_get_msg_txt("no", "No");
		$to      = $man->getNotificationEmailTo() ?: "—";
		$table   = $man->getTableName();

		$p = $container->add_cont_elem("","p");
		//$p->set_style("font-size:0.85em;color:#666;margin-bottom:8px");
		$p->add_cont(
			$this->lng_get_msg_txt("tbl", "Tabla") . ": <b>" . htmlspecialchars($table) . "</b> &nbsp;|&nbsp; " .
			$this->lng_get_msg_txt("mail_enabled", "Notificación email") . ": <b>" . htmlspecialchars($enabled) . "</b> &nbsp;|&nbsp; " .
			$this->lng_get_msg_txt("mail_to", "Destinatario") . ": <b>" . htmlspecialchars($to) . "</b>"
		);
		*/
	}
}
?>
