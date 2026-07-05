<?php
/**
 * DB migrations admin UI.
 *
 * Shows one section per registered module: version, available scripts,
 * pending status. Pending migrations display each parsed SQL statement
 * individually. Statements must be executed in order; statements containing
 * DROP or TRUNCATE require an explicit confirmation via a Bootstrap modal.
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

		// ---- POST handlers --------------------------------------------------

		$applyResult = null;
		$stmtResult  = null;

		// Apply all pending migrations at once.
		if (isset($_POST['dbm_apply']) && $_POST['dbm_apply'] === '1') {
			$applyResult = $man->applyAllPending();
		}

		// Execute a single SQL statement from a specific pending migration.
		if (isset($_POST['dbm_exec_stmt']) && $_POST['dbm_exec_stmt'] === '1') {
			$code = (string)($_POST['dbm_module']   ?? '');
			$num  = (int)   ($_POST['dbm_num']      ?? 0);
			$idx  = (int)   ($_POST['dbm_stmt_idx'] ?? -1);

			if ($migration = $man->getMigrationByNum($code, $num)) {
				$rawSql = @file_get_contents($migration["path"]);
				$stmts  = ($rawSql !== false) ? $man->parseSqlStatements($rawSql) : [];

				if ($idx >= 0 && isset($stmts[$idx])) {
					// Enforce order: all previous statements must be executed first.
					$executed    = $man->getExecutedStatements($code, $num);
					$allPrevDone = true;
					for ($i = 0; $i < $idx; $i++) {
						if (!in_array($i, $executed, true)) {
							$allPrevDone = false;
							break;
						}
					}

					if (!$allPrevDone) {
						$stmtResult = [
							"module" => $code, "num" => $num, "idx" => $idx + 1,
							"name"   => $migration["name"],
							"ok"     => false,
							"error"  => $this->lng_get_msg_txt("dbMigOrderError",
								"Debe ejecutar las sentencias anteriores primero."),
						];
					} else {
						$r = $man->executeSingleStatement($stmts[$idx]);
						if ($r["ok"]) {
							$man->markStatementExecuted($code, $num, $idx);
						}
						$stmtResult = [
							"module" => $code, "num" => $num, "idx" => $idx + 1,
							"name"   => $migration["name"],
							"ok"     => $r["ok"],
							"error"  => $r["error"],
						];
					}
				}
			}
		}

		// Mark a migration as applied without executing its SQL automatically.
		if (isset($_POST['dbm_mark_applied']) && $_POST['dbm_mark_applied'] === '1') {
			$code = (string)($_POST['dbm_module'] ?? '');
			$num  = (int)   ($_POST['dbm_num']    ?? 0);
			if ($migration = $man->getMigrationByNum($code, $num)) {
				$man->saveCurrentVersion($num, $code);
				$man->clearStatementState($code, $num);
				$applyResult = [
					"applied" => ["[" . $code . "] " . $num . " — " . $migration["name"] .
					              " (" . $this->lng_get_msg_txt("dbMigMarkedManually", "marcada manualmente") . ")"],
					"errors"  => [],
					"views"   => null,
				];
			}
		}

		// ---- Result alerts --------------------------------------------------

		if ($stmtResult !== null) {
			$alert = $wrap->add_cont_elem();
			$alert->set_att("role", "alert");
			if ($stmtResult["ok"]) {
				$alert->addClass("alert alert-success alert-dismissible fade show");
				$alert->addCont(
					"<strong><i class='fa fa-check me-1'></i>" .
					$this->lng_get_msg_txt("dbMigStmtOk", "Sentencia ejecutada") .
					"</strong> &mdash; [" . htmlspecialchars($stmtResult["module"]) . "] " .
					"#" . htmlspecialchars($stmtResult["num"]) . " sentencia&nbsp;" . $stmtResult["idx"] .
					'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
				);
			} else {
				$alert->addClass("alert alert-danger alert-dismissible fade show");
				$alert->addCont(
					"<strong><i class='fa fa-times me-1'></i>" .
					$this->lng_get_msg_txt("dbMigStmtError", "Error en sentencia %n%", ["n" => $stmtResult["idx"]]) .
					":</strong> " . htmlspecialchars($stmtResult["error"] ?? "Error desconocido") .
					'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
				);
			}
		}

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
			$pending        = $man->getPendingMigrations($code);
			$modulePending  = count($pending);

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

			// Overview table (all migrations)
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

			// Pending migrations: step-by-step SQL execution
			if (!empty($pending)) {
				$pendingHeader = $cardBody->add_cont_elem();
				$pendingHeader->addClass("px-2 pt-3 pb-1 fw-bold small border-top mt-2");
				$pendingHeader->addCont(
					"<i class='fa fa-terminal me-1 text-warning'></i>" .
					$this->lng_get_msg_txt("dbMigPendingSection", "Migraciones pendientes — ejecución paso a paso")
				);

				foreach ($pending as $m) {
					$rawSql   = @file_get_contents($m["path"]);
					$stmts    = ($rawSql !== false) ? $man->parseSqlStatements($rawSql) : [];
					$executed = $man->getExecutedStatements($code, $m["num"]);
					// Sanitised ID fragment for HTML attributes (no special chars)
					$safeId   = preg_replace('/[^a-zA-Z0-9]/', '-', $code) . '-' . $m["num"];

					$mCard = $cardBody->add_cont_elem();
					$mCard->addClass("border rounded mx-2 mb-3");

					$mCardHead = $mCard->add_cont_elem();
					$mCardHead->addClass("px-3 py-2 bg-light border-bottom d-flex align-items-center gap-2");
					$mCardHead->addCont(
						"<span class='badge bg-secondary me-1'>" . $m["num"] . "</span>" .
						"<strong>" . htmlspecialchars($m["name"]) . "</strong>"
					);

					$mCardBody = $mCard->add_cont_elem();
					$mCardBody->addClass("p-3");

					if (empty($stmts)) {
						$mCardBody->addCont(
							"<span class='text-muted small'>" .
							$this->lng_get_msg_txt("dbMigNoStmts", "Sin sentencias SQL.") .
							"</span>"
						);
					} else {
						foreach ($stmts as $idx => $stmt) {
							$stmtNum     = $idx + 1;
							$isDone      = in_array($idx, $executed, true);
							$isUnlocked  = ($idx === 0) ||
							               in_array($idx - 1, $executed, true);
							$isDangerous = (bool) preg_match('/\b(drop|truncate)\b/i', $stmt);
							$modalId     = "dbm-modal-" . $safeId . "-" . $idx;
							$formId      = "dbm-form-"  . $safeId . "-" . $idx;
							$pageUrl     = htmlspecialchars($this->get_url());

							$stmtWrap = $mCardBody->add_cont_elem();
							$stmtWrap->addClass("mb-3");

							// Label row: "Sentencia N [✓ Ejecutada]"
							$stmtLabel = $stmtWrap->add_cont_elem();
							$stmtLabel->addClass("d-flex align-items-center gap-2 mb-1");
							$labelHtml = "<span class='fw-bold small text-muted'>" .
								$this->lng_get_msg_txt("dbMigStmtLabel", "Sentencia %n%", ["n" => $stmtNum]) .
								"</span>";
							if ($isDone) {
								$labelHtml .= " <span class='badge bg-success'><i class='fa fa-check me-1'></i>" .
									$this->lng_get_msg_txt("dbMigStmtDone", "Ejecutada") . "</span>";
							} elseif ($isDangerous) {
								$labelHtml .= " <span class='badge bg-danger'><i class='fa fa-exclamation-triangle me-1'></i>DROP</span>";
							}
							$stmtLabel->addCont($labelHtml);

							// SQL code block
							$pre = $stmtWrap->add_cont_elem(false, "pre");
							$pre->addClass("bg-dark text-light p-2 rounded small mb-2");
							$pre->set_att("style", "white-space:pre-wrap;word-break:break-all;max-height:200px;overflow-y:auto;");
							$pre->addCont(htmlspecialchars($stmt));

							if ($isDone) {
								// Already executed — no button.
								continue;
							}

							// Hidden form (always present; submitted by button or by modal confirm)
							$formHtml =
								"<form id='" . $formId . "' method='post' action='" . $pageUrl . "' style='display:none'>" .
								"<input type='hidden' name='dbm_exec_stmt' value='1'>" .
								"<input type='hidden' name='dbm_module' value='" . htmlspecialchars($code) . "'>" .
								"<input type='hidden' name='dbm_num' value='" . $m["num"] . "'>" .
								"<input type='hidden' name='dbm_stmt_idx' value='" . $idx . "'>" .
								"</form>";
							$stmtWrap->addCont($formHtml);

							if (!$isUnlocked) {
								// Locked: previous statement not yet executed.
								$stmtWrap->addCont(
									"<button class='btn btn-sm btn-secondary' disabled title='" .
									htmlspecialchars($this->lng_get_msg_txt("dbMigStmtLocked",
										"Ejecute la sentencia anterior primero.")) .
									"'><i class='fa fa-lock me-1'></i>" .
									$this->lng_get_msg_txt("dbMigExecStmt", "Ejecutar sentencia %n%", ["n" => $stmtNum]) .
									"</button>"
								);
							} elseif ($isDangerous) {
								// Dangerous: trigger modal for confirmation.
								$stmtWrap->addCont(
									"<button type='button' class='btn btn-sm btn-danger' " .
									"data-bs-toggle='modal' data-bs-target='#" . $modalId . "'>" .
									"<i class='fa fa-exclamation-triangle me-1'></i>" .
									$this->lng_get_msg_txt("dbMigExecStmt", "Ejecutar sentencia %n%", ["n" => $stmtNum]) .
									"</button>"
								);

								// Confirmation modal
								$stmtWrap->addCont(
									"<div class='modal fade' id='" . $modalId . "' tabindex='-1'>" .
									"<div class='modal-dialog modal-dialog-centered'>" .
									"<div class='modal-content border-danger'>" .

									"<div class='modal-header bg-danger text-white'>" .
									"<h5 class='modal-title'><i class='fa fa-exclamation-triangle me-2'></i>" .
									htmlspecialchars($this->lng_get_msg_txt("dbMigDropWarningTitle",
										"Advertencia: sentencia destructiva")) .
									"</h5>" .
									"<button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>" .
									"</div>" .

									"<div class='modal-body'>" .
									"<p>" .
									htmlspecialchars($this->lng_get_msg_txt("dbMigDropWarningBody",
										"Esta sentencia contiene DROP o TRUNCATE y puede eliminar datos permanentemente. Revísela antes de continuar.")) .
									"</p>" .
									"<pre class='bg-dark text-light p-2 rounded small' style='white-space:pre-wrap;word-break:break-all;max-height:150px;overflow-y:auto;'>" .
									htmlspecialchars($stmt) .
									"</pre>" .
									"</div>" .

									"<div class='modal-footer'>" .
									"<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>" .
									htmlspecialchars($this->lng_get_msg_txt("cancel", "Cancelar")) .
									"</button>" .
									"<button type='button' class='btn btn-danger' " .
									"onclick=\"document.getElementById('" . $formId . "').submit();\">" .
									"<i class='fa fa-exclamation-triangle me-1'></i>" .
									htmlspecialchars($this->lng_get_msg_txt("dbMigDropConfirm",
										"Sí, ejecutar")) .
									"</button>" .
									"</div>" .

									"</div></div></div>"
								);
							} else {
								// Normal unlocked statement.
								$stmtWrap->addCont(
									"<button type='button' class='btn btn-sm btn-outline-primary' " .
									"onclick=\"document.getElementById('" . $formId . "').submit();\">" .
									"<i class='fa fa-play me-1'></i>" .
									$this->lng_get_msg_txt("dbMigExecStmt", "Ejecutar sentencia %n%", ["n" => $stmtNum]) .
									"</button>"
								);
							}
						}
					}

					// "Mark as applied" — only shown once all statements are executed.
					$allDone = (count($executed) >= count($stmts)) && !empty($stmts);
					$markWrap = $mCardBody->add_cont_elem();
					$markWrap->addClass("border-top pt-3 mt-1 d-flex gap-2 align-items-center flex-wrap");

					if (!$allDone) {
						$markWrap->addCont(
							"<span class='text-muted small'>" .
							$this->lng_get_msg_txt("dbMigMarkHint",
								"Ejecute todas las sentencias y luego marque la migración como aplicada:") .
							"</span>"
						);
					}
					$markWrap->addCont(
						"<form method='post' action='" . htmlspecialchars($this->get_url()) . "' style='display:inline'>" .
						"<input type='hidden' name='dbm_mark_applied' value='1'>" .
						"<input type='hidden' name='dbm_module' value='" . htmlspecialchars($code) . "'>" .
						"<input type='hidden' name='dbm_num' value='" . $m["num"] . "'>" .
						"<button type='submit' class='btn btn-sm " . ($allDone ? "btn-success" : "btn-outline-secondary") . "'>" .
						"<i class='fa fa-check me-1'></i>" .
						$this->lng_get_msg_txt("dbMigMarkApplied", "Marcar como aplicada") .
						"</button>" .
						"</form>"
					);
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
