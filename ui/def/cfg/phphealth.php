<?php
/**
 * PHP health and security admin page.
 *
 * Renders the report produced by mwmod_mw_util_phphealth inside the
 * Configuration section of the admin interface. The page is read-only: it
 * inspects the running PHP build and its INI directives, it never changes them.
 *
 * All recommended values and php.net links come from the utility class, so the
 * UI only formats and groups what the ruleset returns.
 *
 * Class name -> file: mwmod_mw_ui_def_cfg_phphealth -> modules/mw/ui/def/cfg/phphealth.php
 */
class mwmod_mw_ui_def_cfg_phphealth extends mwmod_mw_ui_base_basesubui {

	/**
	 * Initializes the subinterface.
	 *
	 * @param string $cod Subinterface code.
	 * @param mwmod_mw_ui_sub_uiabs $parent Parent subinterface.
	 */
	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("phpHealth", "Salud y seguridad de PHP"));
	}

	/**
	 * Sets the sidebar icon.
	 *
	 * @param mwmod_mw_mnu_items_dropdown1 $item Menu item.
	 *
	 * @return void
	 */
	function prepare_mnu_item($item) {
		$item->addInnerHTML_icon("fa fa-shield-alt");
	}

	/**
	 * Outputs the report page.
	 *
	 * @return void
	 */
	function do_exec_page_in() {
		$util   = new mwmod_mw_util_phphealth();
		$report = $util->get_report();

		$MainContainer = $this->get_ui_dom_elem_container();
		$container     = $MainContainer;

		if ($this->mainPanelEnabled && ($mainpanel = $this->createMainPanel())) {
			$MainContainer->add_cont($mainpanel);
			$container = $mainpanel->panel_body->add_cont_elem();
		}

		$wrap = $container->add_cont_elem();
		$wrap->addClass("p-3");

		$this->_render_summary($wrap, $report);
		$this->_render_version_support($wrap, $report);

		foreach ($report["groups"] as $groupCod => $groupTitle) {
			$this->_render_group($wrap, $groupCod, $groupTitle, $report["checks"]);
		}

		$this->_render_footer($wrap, $report);

		echo $MainContainer->get_as_html();
	}

	// ------------------------------------------------------------- rendering

	/**
	 * Renders the main summary card: score, counters and environment.
	 *
	 * @param mwmod_mw_html_elem $wrap Container element.
	 * @param array<string,mixed> $report Report from mwmod_mw_util_phphealth.
	 *
	 * @return void
	 */
	private function _render_summary($wrap, $report) {
		$summary = $report["summary"];
		$env     = $report["environment"];

		$card = $wrap->add_cont_elem();
		$card->addClass("card mb-3");

		$head = $card->add_cont_elem();
		$head->addClass("card-header d-flex flex-wrap align-items-center gap-2");
		$head->addCont(
			"<i class='fa fa-shield-alt me-1 text-secondary'></i>" .
			"<strong>" . $this->_e($this->lng_get_msg_txt("phpHealthTitle", "Salud y seguridad de PHP")) . "</strong>" .
			"<span class='badge bg-light text-dark border ms-1'>" .
			$this->_e($this->lng_get_msg_txt("phpHealthRules", "Reglas v%v% (%d%)", array(
				"v" => $report["version"],
				"d" => $report["date"],
			))) .
			"</span>" .
			"<a class='btn btn-sm btn-outline-secondary ms-auto' href='" . $this->_e($this->get_url()) . "'>" .
			"<i class='fa fa-sync-alt me-1'></i>" . $this->_e($this->lng_get_msg_txt("phpHealthReload", "Volver a analizar")) .
			"</a>"
		);

		$body = $card->add_cont_elem();
		$body->addClass("card-body");

		// Global status badge + counters
		$row = $body->add_cont_elem();
		$row->addClass("d-flex flex-wrap align-items-center gap-3 mb-3");

		$row->addCont($this->_global_badge($summary["status"]));

		$counters = array(
			mwmod_mw_util_phphealth::STATUS_OK   => "success",
			mwmod_mw_util_phphealth::STATUS_WARN => "warning text-dark",
			mwmod_mw_util_phphealth::STATUS_FAIL => "danger",
			mwmod_mw_util_phphealth::STATUS_INFO => "info text-dark",
			mwmod_mw_util_phphealth::STATUS_NA   => "secondary",
		);
		foreach ($counters as $status => $class) {
			$count = $summary["counts"][$status];
			$row->addCont(
				"<span class='badge bg-" . $class . "'>" .
				$this->_e($this->_status_label($status)) . ": " . (int) $count .
				"</span>"
			);
		}

		// Compliance score
		$score = (int) $summary["score"];
		$scoreWrap = $body->add_cont_elem();
		$scoreWrap->addClass("mb-3");

		$scoreLabel = $scoreWrap->add_cont_elem();
		$scoreLabel->addClass("small text-muted mb-1");
		$scoreLabel->addCont(
			$this->_e($this->lng_get_msg_txt("phpHealthScore", "Cumplimiento de las comprobaciones evaluables")) .
			": <strong>" . $score . "%</strong>" .
			" <span class='text-muted'>(" . (int) $summary["counts"][mwmod_mw_util_phphealth::STATUS_OK] .
			" / " . (int) $summary["evaluated"] . ")</span>"
		);

		$progress = $scoreWrap->add_cont_elem();
		$progress->addClass("progress");
		$progress->set_style("height", "16px");

		$bar = $progress->add_cont_elem();
		$bar->addClass("progress-bar " . $this->_score_bar_class($summary["status"]));
		$bar->set_style("width", $score . "%");
		$bar->addCont($score . "%");

		// Environment table
		$table = $body->add_cont_elem(false, "table");
		$table->addClass("table table-sm table-bordered mb-0");

		$rows = array(
			$this->lng_get_msg_txt("phpHealthEnvVersion", "Versión de PHP")      => $env["php_version"] . " (ID " . $env["php_version_id"] . ")",
			$this->lng_get_msg_txt("phpHealthEnvSapi", "SAPI")                    => $env["sapi"],
			$this->lng_get_msg_txt("phpHealthEnvOs", "Sistema operativo")         => $env["os"] . " (" . $env["os_family"] . ", " . $env["architecture"] . ")",
			$this->lng_get_msg_txt("phpHealthEnvIni", "php.ini cargado")          => $env["ini_file"] ? $env["ini_file"] : $this->lng_get_msg_txt("phpHealthEnvIniNone", "(ninguno)"),
			$this->lng_get_msg_txt("phpHealthEnvExt", "Extensiones cargadas")     => (string) $env["loaded_extensions"],
			$this->lng_get_msg_txt("phpHealthEnvHttps", "Petición por HTTPS")     => $env["https"] ? "Sí" : "No",
		);
		$tbody = $table->add_cont_elem(false, "tbody");
		foreach ($rows as $label => $value) {
			$tr = $tbody->add_cont_elem(false, "tr");
			$th = $tr->add_cont_elem($this->_e($label), "th");
			$th->addClass("w-25 fw-normal text-muted");
			$tr->add_cont_elem($this->_e((string) $value), "td");
		}
	}

	/**
	 * Renders the PHP branch support card.
	 *
	 * @param mwmod_mw_html_elem $wrap Container element.
	 * @param array<string,mixed> $report Report from mwmod_mw_util_phphealth.
	 *
	 * @return void
	 */
	private function _render_version_support($wrap, $report) {
		$support = $report["version_support"];

		$card = $wrap->add_cont_elem();
		$card->addClass("card mb-3");

		$head = $card->add_cont_elem();
		$head->addClass("card-header");
		$head->addCont(
			"<i class='fa fa-code-branch me-1 text-secondary'></i>" .
			"<strong>" . $this->_e($this->lng_get_msg_txt("phpHealthBranchSupport", "Soporte de versiones de PHP (php.net)")) . "</strong>"
		);

		$body = $card->add_cont_elem();
		$body->addClass("card-body p-2");

		$table = $body->add_cont_elem(false, "table");
		$table->addClass("table table-sm table-bordered mb-2");

		$thead = $table->add_cont_elem(false, "thead");
		$trHead = $thead->add_cont_elem(false, "tr");
		foreach (array(
			$this->lng_get_msg_txt("phpHealthBranch", "Rama"),
			$this->lng_get_msg_txt("phpHealthRelease", "Publicación"),
			$this->lng_get_msg_txt("phpHealthActiveEnd", "Soporte activo hasta"),
			$this->lng_get_msg_txt("phpHealthSecurityEnd", "Seguridad hasta"),
			$this->lng_get_msg_txt("phpHealthMinPatch", "Parche mínimo 21/10/2026"),
			$this->lng_get_msg_txt("phpHealthStatus", "Estado"),
		) as $headerText) {
			$trHead->add_cont_elem($this->_e($headerText), "th");
		}

		$tbody = $table->add_cont_elem(false, "tbody");
		foreach ($report["branch_table"] as $branch => $branchData) {
			$isCurrent = ($branch === $support["branch"]);
			$status    = $branchData["status"];
			if (!$isCurrent and $status === "eol") {
				continue;
			}

			$tr = $tbody->add_cont_elem(false, "tr");
			if ($isCurrent) {
				$tr->addClass("fw-bold " . $this->_row_class($status));
			}

			$branchCell = $tr->add_cont_elem("", "td");
			$branchCell->addCont(
				$this->_e($branch) .
				($isCurrent ? " <span class='badge bg-dark'>" . $this->_e($this->lng_get_msg_txt("phpHealthInUse", "en uso")) . "</span>" : "")
			);

			$tr->add_cont_elem($this->_e((string) $branchData["release"]), "td");
			$tr->add_cont_elem($this->_e((string) $branchData["active_end"]), "td");
			$tr->add_cont_elem($this->_e((string) $branchData["security_end"]), "td");

			$patch = $branchData["min_patch_cve"];
			$tr->add_cont_elem($patch ? $this->_e((string) $patch) : "&mdash;", "td");

			$statusCell = $tr->add_cont_elem(false, "td");
			$statusCell->addCont($this->_status_badge($this->_branch_status_code($status)));
		}

		// CVE warning / patch result
		$note = $body->add_cont_elem();
		$note->addClass("small text-muted px-1");
		if ($support["min_patch_cve"]) {
			if ($support["patch_ok"]) {
				$note->addCont(
					"<i class='fa fa-check text-success me-1'></i>" .
					$this->_e($this->lng_get_msg_txt(
						"phpHealthPatchOk",
						"La versión en uso incluye la corrección de seguridad del 21/10/2026 (CVE-2026-109618 y CVE-2026-109619)."
					))
				);
			} else {
				$note->addCont(
					"<i class='fa fa-exclamation-triangle text-danger me-1'></i>" .
					$this->_e($this->lng_get_msg_txt(
						"phpHealthPatchKo",
						"La versión en uso es anterior al parche %m% y está afectada por CVE-2026-109618 y CVE-2026-109619: actualizar de inmediato.",
						array("m" => $support["min_patch_cve"])
					))
				);
			}
		} else {
			$note->addCont(
				"<i class='fa fa-info-circle text-info me-1'></i>" .
				$this->_e($this->lng_get_msg_txt(
					"phpHealthPatchUnknown",
					"El conjunto de reglas no tiene un parche mínimo para esta rama."
				))
			);
		}
		$note->addCont(
			" <a href='https://www.php.net/supported-versions.php' target='_blank' rel='noopener'>php.net</a>"
		);
	}

	/**
	 * Renders one group card with its checks table.
	 *
	 * @param mwmod_mw_html_elem $wrap Container element.
	 * @param string $groupCod Group code.
	 * @param string $groupTitle Group title.
	 * @param array<int,array<string,mixed>> $checks All checks.
	 *
	 * @return void
	 */
	private function _render_group($wrap, $groupCod, $groupTitle, $checks) {
		$groupChecks = array();
		foreach ($checks as $check) {
			if ($check["group"] === $groupCod) {
				$groupChecks[] = $check;
			}
		}
		if (empty($groupChecks)) {
			return;
		}

		$card = $wrap->add_cont_elem();
		$card->addClass("card mb-3");

		$head = $card->add_cont_elem();
		$head->addClass("card-header d-flex flex-wrap align-items-center gap-2");
		$head->addCont(
			"<i class='fa fa-list-check me-1 text-secondary'></i>" .
			"<strong>" . $this->_e($groupTitle) . "</strong>"
		);

		foreach (array(
			mwmod_mw_util_phphealth::STATUS_FAIL => "danger",
			mwmod_mw_util_phphealth::STATUS_WARN => "warning text-dark",
		) as $status => $class) {
			$count = 0;
			foreach ($groupChecks as $check) {
				if ($check["status"] === $status) {
					$count++;
				}
			}
			if ($count) {
				$head->addCont(
					"<span class='badge bg-" . $class . "'>" .
					$this->_e($this->_status_label($status)) . ": " . $count .
					"</span>"
				);
			}
		}

		$body = $card->add_cont_elem();
		$body->addClass("card-body p-2");

		$table = $body->add_cont_elem(false, "table");
		$table->addClass("table table-sm table-bordered align-middle mb-0");

		$thead = $table->add_cont_elem(false, "thead");
		$trHead = $thead->add_cont_elem(false, "tr");
		foreach (array(
			$this->lng_get_msg_txt("phpHealthStatus", "Estado"),
			$this->lng_get_msg_txt("phpHealthCheck", "Comprobación"),
			$this->lng_get_msg_txt("phpHealthCurrent", "Valor actual"),
			$this->lng_get_msg_txt("phpHealthRecommended", "Valor recomendado"),
			$this->lng_get_msg_txt("phpHealthHelp", "Por qué importa"),
		) as $headerText) {
			$trHead->add_cont_elem($this->_e($headerText), "th");
		}

		$tbody = $table->add_cont_elem(false, "tbody");
		foreach ($groupChecks as $check) {
			$tr = $tbody->add_cont_elem(false, "tr");

			$statusCell = $tr->add_cont_elem(false, "td");
			$statusCell->addCont($this->_status_badge($check["status"]));

			$labelCell = $tr->add_cont_elem(false, "td");
			$labelCell->addCont(
				"<strong>" . $this->_e($this->lng_get_msg_txt("phpHealth_" . $check["code"], $check["label"])) . "</strong>"
			);

			$currentCell = $tr->add_cont_elem(false, "td");
			$currentCell->addCont("<code>" . $this->_e((string) $check["current"]) . "</code>");

			$expectedCell = $tr->add_cont_elem(false, "td");
			$expectedCell->addCont($this->_e((string) $check["expected"]));

			$helpCell = $tr->add_cont_elem(false, "td");
			$helpCell->addClass("small text-muted");
			$helpHtml = $this->_e((string) $check["help"]);
			if ($check["url"]) {
				$helpHtml .= " <a href='" . $this->_e($check["url"]) . "' target='_blank' rel='noopener'>php.net</a>";
			}
			$helpCell->addCont($helpHtml);
		}
	}

	/**
	 * Renders the rules identity footer.
	 *
	 * @param mwmod_mw_html_elem $wrap Container element.
	 * @param array<string,mixed> $report Report from mwmod_mw_util_phphealth.
	 *
	 * @return void
	 */
	private function _render_footer($wrap, $report) {
		$footer = $wrap->add_cont_elem();
		$footer->addClass("small text-muted px-1 pb-3");
		$footer->addCont(
			$this->_e($this->lng_get_msg_txt(
				"phpHealthFooter",
				"Conjunto de reglas v%v% (%d%). Los valores recomendados provienen de la documentación de php.net y se revisan manualmente; esta página no modifica la configuración del servidor.",
				array("v" => $report["version"], "d" => $report["date"])
			))
		);
	}

	// ----------------------------------------------------------------- badges

	/**
	 * Returns the badge HTML for a check status.
	 *
	 * @param string $status One of the mwmod_mw_util_phphealth::STATUS_* values.
	 *
	 * @return string HTML.
	 */
	private function _status_badge($status) {
		$map = array(
			mwmod_mw_util_phphealth::STATUS_OK   => array("success", "fa fa-check"),
			mwmod_mw_util_phphealth::STATUS_WARN => array("warning text-dark", "fa fa-exclamation-triangle"),
			mwmod_mw_util_phphealth::STATUS_FAIL => array("danger", "fa fa-times"),
			mwmod_mw_util_phphealth::STATUS_INFO => array("info text-dark", "fa fa-info-circle"),
			mwmod_mw_util_phphealth::STATUS_NA   => array("secondary", "fa fa-minus"),
		);
		if (!isset($map[$status])) {
			$status = mwmod_mw_util_phphealth::STATUS_INFO;
		}
		$class = $map[$status][0];
		$icon  = $map[$status][1];
		return "<span class='badge bg-" . $class . "'><i class='" . $icon . " me-1'></i>" .
			$this->_e($this->_status_label($status)) . "</span>";
	}

	/**
	 * Returns the global status badge HTML.
	 *
	 * @param string $global One of "ok", "warning", "danger".
	 *
	 * @return string HTML.
	 */
	private function _global_badge($global) {
		if ($global === "danger") {
			return "<span class='badge bg-danger fs-6'><i class='fa fa-times me-1'></i>" .
				$this->_e($this->lng_get_msg_txt("phpHealthGlobalDanger", "Requiere atención")) . "</span>";
		}
		if ($global === "warning") {
			return "<span class='badge bg-warning text-dark fs-6'><i class='fa fa-exclamation-triangle me-1'></i>" .
				$this->_e($this->lng_get_msg_txt("phpHealthGlobalWarning", "Mejorable")) . "</span>";
		}
		return "<span class='badge bg-success fs-6'><i class='fa fa-check me-1'></i>" .
			$this->_e($this->lng_get_msg_txt("phpHealthGlobalOk", "Configuración correcta")) . "</span>";
	}

	/**
	 * Returns the localized label of a check status.
	 *
	 * @param string $status One of the mwmod_mw_util_phphealth::STATUS_* values.
	 *
	 * @return string Label.
	 */
	private function _status_label($status) {
		switch ($status) {
			case mwmod_mw_util_phphealth::STATUS_OK:
				return $this->lng_get_msg_txt("phpHealthOk", "Correcto");
			case mwmod_mw_util_phphealth::STATUS_WARN:
				return $this->lng_get_msg_txt("phpHealthWarn", "Aviso");
			case mwmod_mw_util_phphealth::STATUS_FAIL:
				return $this->lng_get_msg_txt("phpHealthFail", "Crítico");
			case mwmod_mw_util_phphealth::STATUS_NA:
				return $this->lng_get_msg_txt("phpHealthNa", "No aplica");
			default:
				return $this->lng_get_msg_txt("phpHealthInfo", "Info");
		}
	}

	/**
	 * Maps a branch support status to a check status code.
	 *
	 * @param string $branchStatus "active", "security", "eol" or "unknown".
	 *
	 * @return string One of the mwmod_mw_util_phphealth::STATUS_* values.
	 */
	private function _branch_status_code($branchStatus) {
		switch ($branchStatus) {
			case "active":
				return mwmod_mw_util_phphealth::STATUS_OK;
			case "security":
				return mwmod_mw_util_phphealth::STATUS_WARN;
			case "eol":
				return mwmod_mw_util_phphealth::STATUS_FAIL;
			default:
				return mwmod_mw_util_phphealth::STATUS_INFO;
		}
	}

	/**
	 * Bootstrap progress bar class for the global status.
	 *
	 * @param string $global One of "ok", "warning", "danger".
	 *
	 * @return string CSS classes.
	 */
	private function _score_bar_class($global) {
		if ($global === "danger") {
			return "bg-danger";
		}
		if ($global === "warning") {
			return "bg-warning";
		}
		return "bg-success";
	}

	/**
	 * Bootstrap row class for the current branch row.
	 *
	 * @param string $branchStatus "active", "security", "eol" or "unknown".
	 *
	 * @return string CSS class.
	 */
	private function _row_class($branchStatus) {
		switch ($branchStatus) {
			case "active":
				return "table-success";
			case "security":
				return "table-warning";
			case "eol":
				return "table-danger";
			default:
				return "table-info";
		}
	}

	/**
	 * Escapes a value for HTML output.
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Escaped value.
	 */
	private function _e($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
	}
}
?>
