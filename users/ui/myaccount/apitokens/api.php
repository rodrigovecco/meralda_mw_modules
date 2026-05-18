<?php
/**
 * My Account — Tokens — API
 *
 * Manage personal API tokens:
 *   - List active tokens
 *   - Create a new token (label + expiry + REQUIRED permission scope)
 *   - Revoke (Bootstrap 5 modal)
 *
 * Security:
 *   - Permissions MUST be explicitly declared for every token.
 *     There is no "unrestricted" mode — an empty selection is rejected.
 *   - The selectable list is filtered to permissions the user actually has,
 *     and the server re-filters the submitted list to block privilege escalation.
 *
 * The plaintext token is shown only once, right after creation.
 */
class mwmod_mw_users_ui_myaccount_apitokens_api extends mwmod_mw_users_ui_myaccount_abs {

	function __construct($cod, $parent) {
		$this->init_as_subinterface($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("apiTokens", "Tokens de API"));
	}

	function is_allowed() {
		if (!$user = $this->get_current_user()) {
			return false;
		}
		if (!$user->man->getApitokenMan()) {
			return false;
		}
		return $this->allow("owntoken");
	}

	function do_exec_page_in() {
		if (!$user = $this->get_current_user()) {
			return false;
		}

		$apitokenMan    = $user->man->getApitokenMan();
		$permissionsMan = $user->man->get_permission_man();

		$newRawToken = null;
		$successMsg  = null;
		$errorMsg    = null;

		$action = isset($_POST["_apitk_action"]) ? (string)$_POST["_apitk_action"] : "";

		// --- Create token ---
		if ($action === "create") {
			$label      = trim(strip_tags(isset($_POST["_apitk_label"]) ? (string)$_POST["_apitk_label"] : ""));
			$expiryVal  = trim(isset($_POST["_apitk_expiry"]) ? (string)$_POST["_apitk_expiry"] : "");
			$expiryDays = ($expiryVal !== "" && ctype_digit($expiryVal) && (int)$expiryVal > 0)
				? (int)$expiryVal
				: null;

			// Permissions are mandatory: filter the submitted list against the user's own.
			$permissions = [];
			$raw = isset($_POST["_apitk_perms"]) && is_array($_POST["_apitk_perms"])
				? $_POST["_apitk_perms"]
				: [];
			foreach ($raw as $code) {
				$code = (string)$code;
				if ($code !== "" && $this->allow($code)) {
					$permissions[] = $code;
				}
			}

			if ($label === "") {
				$errorMsg = $this->lng_get_msg_txt("apiTokenLabelRequired", "El nombre del token es requerido.");
			} else if (empty($permissions)) {
				$errorMsg = $this->lng_get_msg_txt(
					"apiTokenPermissionsRequired",
					"Debes seleccionar al menos un permiso para el token."
				);
			} else {
				$result = $apitokenMan->createToken($user->get_id(), $label, $permissions, $expiryDays);
				if ($result) {
					$newRawToken = $result["token"];
				} else {
					$errorMsg = $this->lng_get_msg_txt("apiTokenCreateError", "Error al crear el token.");
				}
			}
		}

		// --- Revoke token ---
		if ($action === "revoke") {
			$tokenId = isset($_POST["_apitk_id"]) ? (int)$_POST["_apitk_id"] : 0;
			if ($tokenId > 0) {
				$tokenItem = $apitokenMan->get_item($tokenId);
				if ($tokenItem && $tokenItem->getUserId() === $user->get_id()) {
					if ($tokenItem->revoke()) {
						$successMsg = $this->lng_get_msg_txt("apiTokenRevoked", "Token revocado correctamente.");
					} else {
						$errorMsg = $this->lng_get_msg_txt("apiTokenRevokeError", "No se pudo revocar el token.");
					}
				}
			}
		}

		// Permissions available to the current user (for the selector)
		$availablePerms = [];
		if ($permissionsMan) {
			$allItems = $permissionsMan->get_items() ?: [];
			foreach ($allItems as $p) {
				$pCod = $p->get_code();
				if (!$pCod) continue;
				if (!$this->allow($pCod)) continue;
				$pLabel = $p->get_name();
				if (!$pLabel) $pLabel = $pCod;
				$availablePerms[$pCod] = $pLabel;
			}
		}

		// Reload tokens
		$tokens       = $apitokenMan->getItemsByUser($user->get_id()) ?: [];
		$activeTokens = array_values(array_filter($tokens, function($t) { return $t->isActive(); }));

		$pageUrl       = $this->get_url();
		$revokeModalId = "apitk-revoke-modal";
		$revokeFormId  = "apitk-revoke-form";

		$container = new mwmod_mw_html_elem();
		$wrap      = $container->add_cont_elem();
		$wrap->addClass("p-3");

		// == Banner: newly created token ==
		if ($newRawToken) {
			$banner = $wrap->add_cont_elem();
			$banner->addClass("alert alert-success alert-dismissible fade show");
			$banner->set_att("role", "alert");
			$banner->addCont(
				"<div class='fw-bold mb-2'><i class='fa fa-check-circle me-1'></i>" .
				$this->lng_get_msg_txt("apiTokenCreatedOk", "Token creado. Cópialo ahora — no se mostrará de nuevo.") .
				"</div>" .
				"<div class='mb-1 text-muted small'>" .
				$this->lng_get_msg_txt("apiTokenYourToken", "Tu nuevo token:") .
				"</div>" .
				"<div class='d-flex gap-2 align-items-center flex-wrap mt-1'>" .
				"<code class='flex-grow-1 p-2 bg-white border rounded text-break user-select-all' id='mw-new-token-val' style='word-break:break-all'>" .
				htmlspecialchars($newRawToken) .
				"</code>" .
				"<button type='button' class='btn btn-sm btn-outline-secondary' " .
				"onclick=\"(function(){" .
				"var el=document.getElementById('mw-new-token-val');" .
				"var txt=el.textContent||el.innerText||'';" .
				"if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(txt);}" .
				"else{var ta=document.createElement('textarea');ta.value=txt;ta.style.position='fixed';ta.style.opacity='0';" .
				"document.body.appendChild(ta);ta.focus();ta.select();document.execCommand('copy');document.body.removeChild(ta);}" .
				"})();\">" .
				"<i class='fa fa-copy me-1'></i>" . $this->lng_get_msg_txt("copy", "Copiar") .
				"</button></div>" .
				'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' .
				htmlspecialchars($this->lng_get_msg_txt("close", "Cerrar")) . '"></button>'
			);
		}

		if ($successMsg) {
			$a = $wrap->add_cont_elem();
			$a->addClass("alert alert-success alert-dismissible fade show");
			$a->set_att("role", "alert");
			$a->addCont(htmlspecialchars($successMsg) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
		}
		if ($errorMsg) {
			$a = $wrap->add_cont_elem();
			$a->addClass("alert alert-danger alert-dismissible fade show");
			$a->set_att("role", "alert");
			$a->addCont(htmlspecialchars($errorMsg) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
		}

		// == Active tokens list ==
		$listCard = $wrap->add_cont_elem();
		$listCard->addClass("card mb-4");
		$cardHeader = $listCard->add_cont_elem();
		$cardHeader->addClass("card-header");
		$cardHeader->addCont(
			"<strong><i class='fa fa-key me-2'></i>" .
			$this->lng_get_msg_txt("apiTokensActive", "Tokens activos") .
			"</strong>"
		);

		$cardBody = $listCard->add_cont_elem();
		$cardBody->addClass("card-body p-0");

		if (empty($activeTokens)) {
			$empty = $cardBody->add_cont_elem();
			$empty->addClass("text-muted p-3");
			$empty->addCont($this->lng_get_msg_txt("apiTokenNone", "No tienes tokens activos."));
		} else {
			$tbl = $cardBody->add_cont_elem(false, "table");
			$tbl->addClass("table table-sm table-hover mb-0 align-middle");

			$thead = $tbl->add_cont_elem(false, "thead");
			$thead->addClass("table-light");
			$htr = $thead->add_cont_elem(false, "tr");
			foreach ([
				$this->lng_get_msg_txt("label",      "Nombre"),
				$this->lng_get_msg_txt("scope",      "Scope"),
				$this->lng_get_msg_txt("createdAt",  "Creado"),
				$this->lng_get_msg_txt("lastUsedAt", "Último uso"),
				$this->lng_get_msg_txt("expiresAt",  "Expira"),
				"",
			] as $h) {
				$th = $htr->add_cont_elem(false, "th");
				if ($h) $th->addCont($h);
			}

			$tbody = $tbl->add_cont_elem(false, "tbody");
			foreach ($activeTokens as $tokenItem) {
				$tr = $tbody->add_cont_elem(false, "tr");

				$td = $tr->add_cont_elem(false, "td");
				$td->addCont("<strong>" . htmlspecialchars($tokenItem->getLabel()) . "</strong>");

				// Scope: list every granted permission code as a badge.
				$td = $tr->add_cont_elem(false, "td");
				$tokenPerms = method_exists($tokenItem, "getPermissions") ? $tokenItem->getPermissions() : null;
				if (empty($tokenPerms)) {
					$td->addCont("<span class='badge bg-warning text-dark'>" . $this->lng_get_msg_txt("scopeNone", "Sin permisos") . "</span>");
				} else {
					$badges = "";
					foreach ($tokenPerms as $pc) {
						$badges .= "<span class='badge bg-info text-dark me-1'>" . htmlspecialchars($pc) . "</span>";
					}
					$td->addCont($badges);
				}

				$td = $tr->add_cont_elem(false, "td");
				$td->addCont("<span class='text-muted small'>" . htmlspecialchars($tokenItem->getCreatedAt() ?: "—") . "</span>");

				$td = $tr->add_cont_elem(false, "td");
				$td->addCont("<span class='text-muted small'>" . htmlspecialchars($tokenItem->getLastUsedAt() ?: $this->lng_get_msg_txt("never", "Nunca")) . "</span>");

				$td = $tr->add_cont_elem(false, "td");
				$td->addCont("<span class='text-muted small'>" . htmlspecialchars($tokenItem->getExpiresAt() ?: $this->lng_get_msg_txt("never", "Nunca")) . "</span>");

				$td = $tr->add_cont_elem(false, "td");
				$td->addClass("text-end");
				$revokeBtn = $td->add_cont_elem(false, "button");
				$revokeBtn->set_att("type", "button");
				$revokeBtn->set_att("data-bs-toggle", "modal");
				$revokeBtn->set_att("data-bs-target", "#" . $revokeModalId);
				$revokeBtn->set_att("data-token-id", (string)$tokenItem->get_id());
				$revokeBtn->set_att("data-token-label", $tokenItem->getLabel());
				$revokeBtn->addClass("btn btn-sm btn-outline-danger");
				$revokeBtn->addCont("<i class='fa fa-times me-1'></i>" . $this->lng_get_msg_txt("revoke", "Revocar"));
			}
		}

		// == Form: create new token ==
		$createCard = $wrap->add_cont_elem();
		$createCard->addClass("card");

		$createCardHeader = $createCard->add_cont_elem();
		$createCardHeader->addClass("card-header");
		$createCardHeader->addCont(
			"<strong><i class='fa fa-plus me-2'></i>" .
			$this->lng_get_msg_txt("apiTokenCreateNew", "Crear nuevo token") .
			"</strong>"
		);

		$createCardBody = $createCard->add_cont_elem();
		$createCardBody->addClass("card-body");

		$infoEl = $createCardBody->add_cont_elem();
		$infoEl->addClass("alert alert-info mb-3");
		$infoEl->addCont(
			"<i class='fa fa-info-circle me-1'></i>" .
			$this->lng_get_msg_txt("apiTokenInfo", "Los tokens de API permiten acceso programático a tu cuenta. Mantenlos en secreto y revócalos si ya no los necesitas.")
		);

		$form = $createCardBody->add_cont_elem(false, "form");
		$form->set_att("method", "post");
		$form->set_att("action", $pageUrl);

		$hiddenAction = $form->add_cont_elem(false, "input");
		$hiddenAction->set_dont_close(true);
		$hiddenAction->set_att("type", "hidden");
		$hiddenAction->set_att("name", "_apitk_action");
		$hiddenAction->set_att("value", "create");

		$row = $form->add_cont_elem();
		$row->addClass("row g-3 align-items-end");

		$colLabel = $row->add_cont_elem();
		$colLabel->addClass("col-md-6");
		$labelEl = $colLabel->add_cont_elem(false, "label");
		$labelEl->addClass("form-label");
		$labelEl->set_att("for", "apitk-label");
		$labelEl->addCont($this->lng_get_msg_txt("apiTokenLabel", "Nombre del token"));
		$inputLabel = $colLabel->add_cont_elem(false, "input");
		$inputLabel->set_dont_close(true);
		$inputLabel->set_att("type", "text");
		$inputLabel->set_att("name", "_apitk_label");
		$inputLabel->set_att("id", "apitk-label");
		$inputLabel->set_att("class", "form-control");
		$inputLabel->set_att("required", "required");
		$inputLabel->set_att("maxlength", "160");
		$inputLabel->set_att("placeholder", $this->lng_get_msg_txt("apiTokenLabelPlaceholder", "Ej: Script de backup, App móvil..."));

		$colExpiry = $row->add_cont_elem();
		$colExpiry->addClass("col-md-3");
		$expiryLabelEl = $colExpiry->add_cont_elem(false, "label");
		$expiryLabelEl->addClass("form-label");
		$expiryLabelEl->set_att("for", "apitk-expiry");
		$expiryLabelEl->addCont($this->lng_get_msg_txt("apiTokenExpiryDays", "Expiración (días)"));
		$expiryInput = $colExpiry->add_cont_elem(false, "input");
		$expiryInput->set_dont_close(true);
		$expiryInput->set_att("type", "number");
		$expiryInput->set_att("name", "_apitk_expiry");
		$expiryInput->set_att("id", "apitk-expiry");
		$expiryInput->set_att("class", "form-control");
		$expiryInput->set_att("min", "1");
		$expiryInput->set_att("placeholder", $this->lng_get_msg_txt("apiTokenExpiryNeverPlaceholder", "Sin límite"));

		$colBtn = $row->add_cont_elem();
		$colBtn->addClass("col-md-3");
		$submitBtn = $colBtn->add_cont_elem(false, "button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-primary w-100");
		$submitBtn->addCont("<i class='fa fa-plus me-1'></i>" . $this->lng_get_msg_txt("apiTokenCreate", "Crear token"));

		// == Scope (permissions) selector ==
		// Permissions are mandatory — user must check at least one.
		$scopeWrap = $form->add_cont_elem();
		$scopeWrap->addClass("mt-4 border-top pt-3");

		$scopeTitle = $scopeWrap->add_cont_elem(false, "label");
		$scopeTitle->addClass("form-label fw-bold");
		$scopeTitle->addCont(
			"<i class='fa fa-shield-alt me-1'></i>" .
			$this->lng_get_msg_txt("apiTokenScope", "Permisos del token") .
			" <span class='text-danger'>*</span>"
		);

		$scopeHelp = $scopeWrap->add_cont_elem();
		$scopeHelp->addClass("text-muted small mb-2");
		$scopeHelp->addCont($this->lng_get_msg_txt(
			"apiTokenScopeHelp",
			"Selecciona explicitamente cada permiso que el token podrá usar. Es obligatorio elegir al menos uno."
		));

		$permsList = $scopeWrap->add_cont_elem();
		$permsList->addClass("mt-2");
		$permsList->set_att("id", "apitk-perms-list");

		if (empty($availablePerms)) {
			$noPerm = $permsList->add_cont_elem();
			$noPerm->addClass("alert alert-warning small mb-0");
			$noPerm->addCont($this->lng_get_msg_txt(
				"apiTokenNoAvailablePerms",
				"No hay permisos disponibles para asignar a un token."
			));
		} else {
			foreach ($availablePerms as $pCod => $pLabel) {
				$chkWrap = $permsList->add_cont_elem();
				$chkWrap->addClass("form-check");
				$chk = $chkWrap->add_cont_elem(false, "input");
				$chk->set_dont_close(true);
				$chk->set_att("type", "checkbox");
				$chk->set_att("name", "_apitk_perms[]");
				$chk->set_att("value", $pCod);
				$chk->set_att("id", "apitk-perm-" . htmlspecialchars($pCod));
				$chk->set_att("class", "form-check-input apitk-perm-chk");
				$chkLbl = $chkWrap->add_cont_elem(false, "label");
				$chkLbl->addClass("form-check-label");
				$chkLbl->set_att("for", "apitk-perm-" . htmlspecialchars($pCod));
				$chkLbl->addCont(
					htmlspecialchars($pLabel) .
					" <code class='text-muted small'>" . htmlspecialchars($pCod) . "</code>"
				);
			}
		}

		// == Bootstrap 5 modal: confirm revocation ==
		$modal = $container->add_cont_elem();
		$modal->addClass("modal fade");
		$modal->set_att("id", $revokeModalId);
		$modal->set_att("tabindex", "-1");
		$modal->set_att("aria-labelledby", $revokeModalId . "-label");
		$modal->set_att("aria-hidden", "true");

		$mDialog = $modal->add_cont_elem();
		$mDialog->addClass("modal-dialog modal-dialog-centered modal-sm");
		$mContent = $mDialog->add_cont_elem();
		$mContent->addClass("modal-content");

		$mHeader = $mContent->add_cont_elem();
		$mHeader->addClass("modal-header bg-danger text-white");
		$mTitle = $mHeader->add_cont_elem(false, "h5");
		$mTitle->addClass("modal-title");
		$mTitle->set_att("id", $revokeModalId . "-label");
		$mTitle->addCont(
			"<i class='fa fa-exclamation-triangle me-2'></i>" .
			$this->lng_get_msg_txt("apiTokenRevokeConfirmTitle", "Revocar token")
		);
		$closeBtn = $mHeader->add_cont_elem(false, "button");
		$closeBtn->set_att("type", "button");
		$closeBtn->set_att("data-bs-dismiss", "modal");
		$closeBtn->set_att("aria-label", $this->lng_get_msg_txt("close", "Cerrar"));
		$closeBtn->addClass("btn-close btn-close-white");

		$mBody = $mContent->add_cont_elem();
		$mBody->addClass("modal-body");
		$mBody->addCont(
			$this->lng_get_msg_txt(
				"apiTokenRevokeConfirmBody",
				"¿Revocar el token <strong id='apitk-revoke-lbl'></strong>? Esta acción no se puede deshacer."
			)
		);

		$mFooter = $mContent->add_cont_elem();
		$mFooter->addClass("modal-footer");

		$cancelBtn = $mFooter->add_cont_elem(false, "button");
		$cancelBtn->set_att("type", "button");
		$cancelBtn->set_att("data-bs-dismiss", "modal");
		$cancelBtn->addClass("btn btn-secondary");
		$cancelBtn->addCont($this->lng_get_msg_txt("cancel", "Cancelar"));

		$revokeForm = $mFooter->add_cont_elem(false, "form");
		$revokeForm->set_att("id", $revokeFormId);
		$revokeForm->set_att("method", "post");
		$revokeForm->set_att("action", $pageUrl);

		$hAction = $revokeForm->add_cont_elem(false, "input");
		$hAction->set_dont_close(true);
		$hAction->set_att("type", "hidden");
		$hAction->set_att("name", "_apitk_action");
		$hAction->set_att("value", "revoke");

		$hId = $revokeForm->add_cont_elem(false, "input");
		$hId->set_dont_close(true);
		$hId->set_att("type", "hidden");
		$hId->set_att("name", "_apitk_id");
		$hId->set_att("id", "apitk-revoke-id");
		$hId->set_att("value", "");

		$revokeSubmit = $revokeForm->add_cont_elem(false, "button");
		$revokeSubmit->set_att("type", "submit");
		$revokeSubmit->addClass("btn btn-danger");
		$revokeSubmit->addCont("<i class='fa fa-times me-1'></i>" . $this->lng_get_msg_txt("revoke", "Revocar"));

		echo $container->get_as_html();

		echo '<script>
(function(){
	var modal = document.getElementById(' . json_encode($revokeModalId) . ');
	if (modal) {
		modal.addEventListener("show.bs.modal", function(event) {
			var btn = event.relatedTarget;
			var id  = btn ? btn.getAttribute("data-token-id")    : "";
			var lbl = btn ? btn.getAttribute("data-token-label") : "";
			var idInput = document.getElementById("apitk-revoke-id");
			var lblEl   = document.getElementById("apitk-revoke-lbl");
			if (idInput) idInput.value = id;
			if (lblEl)   lblEl.textContent = lbl;
		});
	}
}());
</script>';

		return true;
	}
}
?>
