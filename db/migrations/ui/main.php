<?php
/**
 * DB migrations admin UI.
 *
 * Shows one section per registered module: version, available scripts,
 * pending status. A single "Apply all pending" button runs all modules
 * in registration order.
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

		$MainContainer = $this->get_ui_dom_elem_container();
		$container     = $MainContainer;

		if ($this->mainPanelEnabled && ($mainpanel = $this->createMainPanel())) {
			$MainContainer->add_cont($mainpanel);
			$container = $mainpanel->panel_body->add_cont_elem();
		}

		$wrap = $container->add_cont_elem();
		$wrap->addClass("p-3");

		if (!$man) {
			$alert = $wrap->add_cont_elem();
			$alert->addClass("alert alert-danger");
			$alert->addCont($this->lng_get_msg_txt("dbMigManUnavailable",
				"El gestor de migraciones no está disponible."));
			echo $MainContainer->get_as_html();
			return;
		}

		// Auto-migrate legacy single-module state key on first load.
		$man->migrateLegacyStateKey();

		// Process "apply all pending" POST action.
		$applyResult = null;
		if (isset($_POST['dbm_apply']) && $_POST['dbm_apply'] === '1') {
			$applyResult = $man->applyAllPending();
		}

		// — Result alerts —
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
			}
			if (!empty($applyResult["views"])) {
				$vr = $applyResult["views"];
				if (!empty($vr["errors"])) {
					$alert = $wrap->add_cont_elem();
					$alert->addClass("alert alert-warning alert-dismissible fade show");
					$alert->set_att("role", "alert");
					$msgs = array_map("htmlspecialchars", $vr["errors"]);
					$alert->addCont(
						"<strong>" .
						$this->lng_get_msg_txt("dbMigViewsErrorTitle", "Errores al aplicar vistas") .
						":</strong><br>" .
						implode("<br>", $msgs) .
						'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
					);
				}
				if (!empty($vr["applied"])) {
					$alert = $wrap->add_cont_elem();
					$alert->addClass("alert alert-info alert-dismissible fade show");
					$alert->set_att("role", "alert");
					$msgs = array_map("htmlspecialchars", $vr["applied"]);
					$alert->addCont(
						"<strong>" .
						$this->lng_get_msg_txt("dbMigViewsAppliedTitle", "Vistas actualizadas") .
						":</strong><br>" .
						implode("<br>", $msgs) .
						'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
					);
				}
			}
		}

		$totalPending      = $man->getTotalPendingCount();
		$totalPendingViews = $man->getTotalPendingViewsCount();

		// — Global apply button —
		if ($totalPending > 0 || $totalPendingViews > 0) {
			$modalId = "dbm-confirm-modal";
			$formId  = "dbm-apply-form";

			if ($totalPending > 0 && $totalPendingViews > 0) {
				$applyBtnLabel = $this->lng_get_msg_txt("dbMigApplyBtnBoth",
					"Aplicar %n% migración(es) y %v% vista(s) pendiente(s)",
					["n" => $totalPending, "v" => $totalPendingViews]);
			} elseif ($totalPending > 0) {
				$applyBtnLabel = $this->lng_get_msg_txt("dbMigApplyBtn",
					"Aplicar %n% migración(es) pendiente(s)",
					["n" => $totalPending]);
			} else {
				$applyBtnLabel = $this->lng_get_msg_txt("dbMigApplyViewsBtn",
					"Aplicar %v% vista(s) pendiente(s)",
					["v" => $totalPendingViews]);
			}

			$triggerBtn = $wrap->add_cont_elem(false, "button");
			$triggerBtn->set_att("type", "button");
			$triggerBtn->set_att("data-bs-toggle", "modal");
			$triggerBtn->set_att("data-bs-target", "#" . $modalId);
			$triggerBtn->addClass("btn btn-warning mb-3");
			$triggerBtn->addCont("<i class='fa fa-play me-1'></i>" . $applyBtnLabel);

			$form = $wrap->add_cont_elem(false, "form");
			$form->set_att("id", $formId);
			$form->set_att("method", "post");
			$form->set_att("action", $this->get_url());
			$hidden = $form->add_cont_elem(false, "input");
			$hidden->set_dont_close(true);
			$hidden->set_att("type", "hidden");
			$hidden->set_att("name", "dbm_apply");
			$hidden->set_att("value", "1");

			$modal = $wrap->add_cont_elem();
			$modal->addClass("modal fade");
			$modal->set_att("id", $modalId);
			$modal->set_att("tabindex", "-1");
			$mDialog  = $modal->add_cont_elem();
			$mDialog->addClass("modal-dialog modal-dialog-centered modal-sm");
			$mContent = $mDialog->add_cont_elem();
			$mContent->addClass("modal-content");

			$mHeader = $mContent->add_cont_elem();
			$mHeader->addClass("modal-header bg-warning text-dark");
			$mTitle  = $mHeader->add_cont_elem(false, "h5");
			$mTitle->addClass("modal-title");
			$mTitle->addCont(
				"<i class='fa fa-exclamation-triangle me-2'></i>" .
				$this->lng_get_msg_txt("dbMigConfirmTitle", "Confirmar aplicación")
			);
			$closeBtn = $mHeader->add_cont_elem(false, "button");
			$closeBtn->set_att("type", "button");
			$closeBtn->set_att("data-bs-dismiss", "modal");
			$closeBtn->set_att("aria-label", $this->lng_get_msg_txt("close", "Cerrar"));
			$closeBtn->addClass("btn-close");

			$mBody = $mContent->add_cont_elem();
			$mBody->addClass("modal-body");
			$mBody->addCont(
				$this->lng_get_msg_txt("dbMigConfirmBody",
					"Se aplicarán los cambios pendientes en todos los módulos. Esta acción no se puede deshacer. ¿Continuar?")
			);

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
				$this->lng_get_msg_txt("dbMigApplyConfirmBtn", "Aplicar (%n%)", ["n" => $totalPending])
			);
		}

		// — One section per module —
		foreach ($man->getModules() as $code => $relPath) {
			$section = $wrap->add_cont_elem();
			$section->addClass("card mb-3");

			$cardHeader = $section->add_cont_elem();
			$cardHeader->addClass("card-header d-flex align-items-center gap-2");
			$cardHeader->addCont(
				"<i class='fa fa-cube me-1 text-secondary'></i>" .
				"<strong>" . htmlspecialchars($code) . "</strong>" .
				"<small class='text-muted ms-1'>" . htmlspecialchars($relPath) . "</small>"
			);

			$cardBody = $section->add_cont_elem();
			$cardBody->addClass("card-body p-2");

			if (!$man->moduleDirectoryExists($code)) {
				$info = $cardBody->add_cont_elem();
				$info->addClass("text-muted small p-2");
				$info->addCont(
					$this->lng_get_msg_txt("dbMigNoDirInfo",
						"Directorio no encontrado: <code>%p%</code>",
						["p" => htmlspecialchars($relPath)])
				);
				continue;
			}

			$currentVersion = $man->getCurrentVersion($code);
			$available      = $man->getAvailableMigrations($code);
			$modulePending  = count($man->getPendingMigrations($code));

			// Status badge row
			$statusRow = $cardBody->add_cont_elem();
			$statusRow->addClass("d-flex align-items-center gap-3 px-2 py-1 border-bottom mb-2");

			$badge = $statusRow->add_cont_elem();
			$badge->addClass("fw-bold text-primary");
			$badge->addCont("v" . $currentVersion);

			$info = $statusRow->add_cont_elem();
			$info->addClass("text-muted small");
			$info->addCont(
				$this->lng_get_msg_txt("dbMigStatusInfo",
					"%total% script(s) &mdash; %pending% pendiente(s)",
					["total" => count($available), "pending" => $modulePending])
			);

			if (empty($available)) {
				$empty = $cardBody->add_cont_elem();
				$empty->addClass("text-muted small px-2");
				$empty->addCont($this->lng_get_msg_txt("dbMigNoScripts",
					"No se encontraron scripts de migración."));
				continue;
			}

			$table = $cardBody->add_cont_elem(false, "table");
			$table->addClass("table table-sm table-bordered mb-0");

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

			// Views subfolder
			if ($man->moduleViewsDirectoryExists($code)) {
				$viewFiles = $man->getViewFiles($code);

				$viewHeader = $cardBody->add_cont_elem();
				$viewHeader->addClass("px-2 pt-2 pb-1 text-muted small fw-bold border-top mt-2");
				$viewHeader->addCont(
					"<i class='fa fa-eye me-1'></i>" .
					$this->lng_get_msg_txt("dbMigViewsSection", "Vistas (%n% script(s))",
						["n" => count($viewFiles)])
				);

				if ($viewFiles) {
					$vtable = $cardBody->add_cont_elem(false, "table");
					$vtable->addClass("table table-sm table-bordered mb-0");

					$vthead  = $vtable->add_cont_elem(false, "thead");
					$vtrHead = $vthead->add_cont_elem(false, "tr");
					foreach ([
						$this->lng_get_msg_txt("dbMigColFile",    "Script"),
						$this->lng_get_msg_txt("dbMigColVersion", "Última"),
						$this->lng_get_msg_txt("dbMigColApplied", "Aplicada"),
					] as $thTxt) {
						$vtrHead->add_cont_elem($thTxt, "th");
					}

					$vtbody = $vtable->add_cont_elem(false, "tbody");
					foreach ($viewFiles as $vf) {
						$appliedVer = $man->getAppliedViewVersion($code, $vf["file"]);
						$upToDate   = $vf["version"] !== null
						             && $appliedVer !== null
						             && $appliedVer === (string)$vf["version"];
						$vtr = $vtbody->add_cont_elem(false, "tr");
						$vtr->add_cont_elem(htmlspecialchars(pathinfo($vf["file"], PATHINFO_FILENAME)), "td");
						$vtdVer = $vtr->add_cont_elem(false, "td");
						if ($vf["version"]) {
							$verBadge = $upToDate ? "bg-success" : "bg-secondary";
							$vtdVer->addCont("<span class='badge {$verBadge}'>" .
								htmlspecialchars($vf["version"]) . "</span>");
						} else {
							$vtdVer->addCont("<span class='text-muted'>&mdash;</span>");
						}
						$vtdApp = $vtr->add_cont_elem(false, "td");
						if ($appliedVer !== null) {
							$badgeClass = $upToDate ? "bg-success" : "bg-warning text-dark";
							$vtdApp->addCont("<span class='badge {$badgeClass}'>" .
								htmlspecialchars($appliedVer) . "</span>");
						} else {
							$vtdApp->addCont("<span class='text-muted'>&mdash;</span>");
						}
					}
				}
			}
		}

		echo $MainContainer->get_as_html();
	}

	// -------------------------------------------------------------------------

	/** @return mwmod_mw_db_migrations_man|false */
	private function _getMigrationsMan() {
		return $this->mainap->get_submanager("dbmigrations");
	}
}
?>