<?php
/**
 * UI de administración para migraciones de base de datos.
 *
 * Muestra la versión aplicada actual, lista los scripts disponibles
 * y pendientes, y permite aplicar todas las migraciones pendientes.
 * Solo accesible por usuarios con permiso "mainadmin".
 */
class mwmod_mw_db_migrations_ui_main extends mwmod_mw_ui_base_basesubuia {

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("dbMigrations", "Migraciones de BD"));
	}

	function is_allowed() {
		return $this->allow("mainadmin");
	}

	function prepare_mnu_item($item) {
		$item->addInnerHTML_icon("fa fa-database");
	}

	// -------------------------------------------------------------------------

	function do_exec_page_in() {
		$man = $this->_getMigrationsMan();

		// Construcción del contenedor principal
		$MainContainer = $this->get_ui_dom_elem_container();
		$container     = $MainContainer;

		if ($this->mainPanelEnabled && ($mainpanel = $this->createMainPanel())) {
			$MainContainer->add_cont($mainpanel);
			$container = $mainpanel->panel_body->add_cont_elem();
		}

		$wrap = $container->add_cont_elem();
		$wrap->addClass("p-3");

		// Manager no disponible (problema de configuración)
		if (!$man) {
			$alert = $wrap->add_cont_elem();
			$alert->addClass("alert alert-danger");
			$alert->addCont($this->lng_get_msg_txt("dbMigManUnavailable",
				"El gestor de migraciones no está disponible."));
			echo $MainContainer->get_as_html();
			return;
		}

		// Carpeta de migraciones inexistente — estado normal en proyectos nuevos
		if (!$man->migrationsDirectoryExists()) {
			$info = $wrap->add_cont_elem();
			$info->addClass("alert alert-info");
			$info->addCont($this->lng_get_msg_txt("dbMigNoDirInfo",
				"No se encontró el directorio de migraciones. Crea <code>src/mwap/db/migrations/</code> y agrega archivos .sql para comenzar."));
			echo $MainContainer->get_as_html();
			return;
		}

		// Procesar acción POST "aplicar pendientes"
		$applyResult = null;
		if (isset($_POST['dbm_apply']) && $_POST['dbm_apply'] === '1') {
			$applyResult = $man->applyAllPending();
		}

		$currentVersion = $man->getCurrentVersion();
		$available      = $man->getAvailableMigrations();
		$pending        = $man->getPendingMigrations();
		$pendingCount   = count($pending);

		// — Resultado de la aplicación —
		if ($applyResult !== null) {
			if (!empty($applyResult["errors"])) {
				$alert = $wrap->add_cont_elem();
				$alert->addClass("alert alert-danger alert-dismissible fade show");
				$alert->set_att("role", "alert");
				$alert->addCont(
					"<strong>" .
					$this->lng_get_msg_txt("dbMigErrorTitle", "Error al aplicar migraciones") .
					":</strong> " .
					htmlspecialchars($applyResult["errors"][0]) .
					'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
				);
			}
			if (!empty($applyResult["applied"])) {
				$alert = $wrap->add_cont_elem();
				$alert->addClass("alert alert-success alert-dismissible fade show");
				$alert->set_att("role", "alert");
				$msgs = array_map("htmlspecialchars", $applyResult["applied"]);
				$alert->addCont(
					"<strong>" .
					$this->lng_get_msg_txt("dbMigAppliedTitle", "Aplicadas correctamente") .
					":</strong><br>" .
					implode("<br>", $msgs) .
					'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
				);
				$currentVersion = $man->getCurrentVersion();
				$pending        = $man->getPendingMigrations();
				$pendingCount   = count($pending);
			}
		}

		// — Tarjeta de estado —
		$statusCard = $wrap->add_cont_elem();
		$statusCard->addClass("card mb-3");
		$statusBody = $statusCard->add_cont_elem();
		$statusBody->addClass("card-body d-flex align-items-center gap-3");

		$badge = $statusBody->add_cont_elem();
		$badge->addClass("fs-4 fw-bold text-primary");
		$badge->addCont("v" . $currentVersion);

		$infoEl = $statusBody->add_cont_elem();
		$total  = count($available);
		$infoEl->addCont(
			"<div class='text-muted'>" .
			$this->lng_get_msg_txt("dbMigStatusInfo",
				"%total% script(s) en total &mdash; %pending% pendiente(s)",
				["total" => $total, "pending" => $pendingCount]) .
			"</div>"
		);

		// — Botón de disparo + modal Bootstrap 5 de confirmación —
		if ($pendingCount > 0) {
			$modalId  = "dbm-confirm-modal";
			$formId   = "dbm-apply-form";

			// Botón que abre el modal (no envía el formulario directamente)
			$triggerBtn = $wrap->add_cont_elem(false, "button");
			$triggerBtn->set_att("type", "button");
			$triggerBtn->set_att("data-bs-toggle", "modal");
			$triggerBtn->set_att("data-bs-target", "#" . $modalId);
			$triggerBtn->addClass("btn btn-warning mb-3");
			$triggerBtn->addCont(
				"<i class='fa fa-play me-1'></i>" .
				$this->lng_get_msg_txt("dbMigApplyBtn",
					"Aplicar %n% migración(es) pendiente(s)",
					["n" => $pendingCount])
			);

			// Formulario oculto (sin botón submit propio)
			$form = $wrap->add_cont_elem(false, "form");
			$form->set_att("id", $formId);
			$form->set_att("method", "post");
			$form->set_att("action", $this->get_url());
			$hidden = $form->add_cont_elem(false, "input");
			$hidden->set_dont_close(true);
			$hidden->set_att("type", "hidden");
			$hidden->set_att("name", "dbm_apply");
			$hidden->set_att("value", "1");

			// Modal de confirmación Bootstrap 5
			$modal = $wrap->add_cont_elem();
			$modal->addClass("modal fade");
			$modal->set_att("id", $modalId);
			$modal->set_att("tabindex", "-1");
			$modal->set_att("aria-labelledby", $modalId . "-label");
			$modal->set_att("aria-hidden", "true");

			$mDialog = $modal->add_cont_elem();
			$mDialog->addClass("modal-dialog modal-dialog-centered modal-sm");

			$mContent = $mDialog->add_cont_elem();
			$mContent->addClass("modal-content");

			// Header
			$mHeader = $mContent->add_cont_elem();
			$mHeader->addClass("modal-header bg-warning text-dark");
			$mTitle = $mHeader->add_cont_elem(false, "h5");
			$mTitle->addClass("modal-title");
			$mTitle->set_att("id", $modalId . "-label");
			$mTitle->addCont(
				"<i class='fa fa-exclamation-triangle me-2'></i>" .
				$this->lng_get_msg_txt("dbMigConfirmTitle", "Confirmar aplicación")
			);
			$closeBtn = $mHeader->add_cont_elem(false, "button");
			$closeBtn->set_att("type", "button");
			$closeBtn->set_att("data-bs-dismiss", "modal");
			$closeBtn->set_att("aria-label", $this->lng_get_msg_txt("close", "Cerrar"));
			$closeBtn->addClass("btn-close");

			// Body
			$mBody = $mContent->add_cont_elem();
			$mBody->addClass("modal-body");
			$mBody->addCont(
				$this->lng_get_msg_txt("dbMigConfirmBody",
					"Se aplicarán <strong>%n%</strong> migración(es) pendiente(s). Esta acción no se puede deshacer. ¿Continuar?",
					["n" => $pendingCount])
			);

			// Footer
			$mFooter = $mContent->add_cont_elem();
			$mFooter->addClass("modal-footer");

			$cancelBtn = $mFooter->add_cont_elem(false, "button");
			$cancelBtn->set_att("type", "button");
			$cancelBtn->set_att("data-bs-dismiss", "modal");
			$cancelBtn->addClass("btn btn-secondary");
			$cancelBtn->addCont($this->lng_get_msg_txt("cancel", "Cancelar"));

			$confirmBtn = $mFooter->add_cont_elem(false, "button");
			$confirmBtn->set_att("type", "button");
			$confirmBtn->set_att("onclick", "document.getElementById('" . $formId . "').submit();");
			$confirmBtn->addClass("btn btn-warning");
			$confirmBtn->addCont(
				"<i class='fa fa-play me-1'></i>" .
				$this->lng_get_msg_txt("dbMigApplyConfirmBtn",
					"Aplicar (%n%)",
					["n" => $pendingCount])
			);
		}

		// — Tabla de migraciones —
		if (!empty($available)) {
			$table = $wrap->add_cont_elem(false, "table");
			$table->addClass("table table-sm table-bordered");

			$thead  = $table->add_cont_elem(false, "thead");
			$trHead = $thead->add_cont_elem(false, "tr");
			foreach ([
				$this->lng_get_msg_txt("dbMigColNum",    "#"),
				$this->lng_get_msg_txt("dbMigColDesc",   "Descripción"),
				$this->lng_get_msg_txt("dbMigColStatus", "Estado"),
			] as $thTxt) {
				$trHead->add_cont_elem($thTxt, "th");
			}

			$tbody = $table->add_cont_elem(false, "tbody");
			foreach ($available as $m) {
				$tr = $tbody->add_cont_elem(false, "tr");
				$tr->add_cont_elem((string)$m["num"], "td");
				$tr->add_cont_elem(htmlspecialchars($m["name"]), "td");

				$tdStatus = $tr->add_cont_elem(false, "td");
				if ($m["num"] <= $currentVersion) {
					$tdStatus->addCont("<span class='badge bg-success'>" .
						$this->lng_get_msg_txt("dbMigApplied", "Aplicada") . "</span>");
				} else {
					$tdStatus->addCont("<span class='badge bg-secondary'>" .
						$this->lng_get_msg_txt("dbMigPendingStatus", "Pendiente") . "</span>");
				}
			}
		} else {
			$empty = $wrap->add_cont_elem();
			$empty->addClass("text-muted");
			$empty->addCont($this->lng_get_msg_txt("dbMigNoScripts",
				"No se encontraron scripts de migración en el directorio."));
		}

		echo $MainContainer->get_as_html();
	}

	// -------------------------------------------------------------------------

	/**
	 * @return mwmod_mw_db_migrations_man|false
	 */
	private function _getMigrationsMan() {
		return $this->mainap->get_submanager("dbmigrations");
	}
}
?>
