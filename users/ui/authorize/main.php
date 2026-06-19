<?php

/**
 * Authorization consent screen.
 *
 * Registered as subinterface 'authorize' on mwmod_mw_ui2_def_main_admin.
 * Reached after mwmod_mw_service_authorize_endpoint stores the pending
 * request in $_SESSION['_mw_authorize_request'] and redirects here.
 *
 * Flow:
 *   GET  /admin/?si=authorize   — show consent form
 *   POST /admin/?si=authorize   — user accepted → create apitoken, redirect or show token
 *
 * Security:
 *   - CSRF-protected via session nonce.
 *   - Scope codes are re-validated against the permission catalog on POST.
 *   - Each code is checked with $user->man->allow() — not just roles/DB.
 *   - The resulting token is created via mwmod_mw_users_apitoken_man::createToken()
 *     which enforces non-empty scope and validates codes against the catalog.
 *   - Token is shown once for manual copy OR sent to redirect_uri as ?token=<jwt>.
 */
class mwmod_mw_users_ui_authorize_main extends mwmod_mw_ui_base_basesubuia {

    const SESSION_KEY = '_mw_authorize_request';

    function __construct($cod, $parent) {
        $this->init_as_main_or_sub($cod, $parent);
        $this->set_def_title("Autorizar acceso");
    }

    function is_allowed() {
        return (bool) $this->get_current_user();
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Read and validate the pending request from session. Returns array or false. */
    private function getSessionRequest() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $data = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($data)) {
            return false;
        }
        // Expire after 10 minutes.
        if ((time() - ($data['created_at'] ?? 0)) > 600) {
            unset($_SESSION[self::SESSION_KEY]);
            return false;
        }
        return $data;
    }

    private function clearSessionRequest() {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * For each requested scope code return status:
     *   'ok'      — user has the permission
     *   'warning' — code exists in catalog but user lacks it
     *   'unknown' — code not in catalog
     *
     * @param string[] $scope
     * @param mwmod_mw_users_user $user
     * @return array<string, array{code:string, status:string}>
     */
    private function buildScopeInfo(array $scope, $user) {
        $permMan   = $user->man->get_permission_man();
        $catalogCodes = [];
        if ($permMan && ($items = $permMan->get_items())) {
            foreach ($items as $p) {
                $catalogCodes[$p->get_code()] = true;
            }
        }
        $result = [];
        foreach ($scope as $code) {
            if (!isset($catalogCodes[$code])) {
                $status = 'unknown';
            } elseif ($user->man->allow($code)) {
                $status = 'ok';
            } else {
                $status = 'warning';
            }
            $result[$code] = ['code' => $code, 'status' => $status];
        }
        return $result;
    }

    // ------------------------------------------------------------------
    // Execution
    // ------------------------------------------------------------------

    function do_exec_page_in() {
        $user = $this->get_current_user();
        if (!$user) {
            return;
        }

        $container = $this->get_ui_dom_elem_container();

        $request = $this->getSessionRequest();
        if (!$request) {
            $alert = $container->add_cont_elem(false, 'div');
            $alert->set_att('class', 'alert alert-danger');
            $alert->addCont('No hay ninguna solicitud de autorización pendiente o ha expirado.');
            $container->do_output();
            return;
        }

        $scope       = $request['scope'];
        $redirectUri = $request['redirect_uri'];
        $label       = $request['label'];
        $nonce       = $request['nonce'];

        // --- Handle POST (user decision) ---
        $action = $_POST['_authz_action'] ?? '';
        if ($action !== '' && isset($_POST['_authz_nonce'])) {

            // CSRF check
            if ($_POST['_authz_nonce'] !== $nonce) {
                $alert = $container->add_cont_elem(false, 'div');
                $alert->set_att('class', 'alert alert-danger');
                $alert->addCont('Error de seguridad (nonce). Vuelve a intentarlo.');
                $container->do_output();
                return;
            }

            if ($action === 'deny') {
                $this->clearSessionRequest();
                $alert = $container->add_cont_elem(false, 'div');
                $alert->set_att('class', 'alert alert-secondary');
                $alert->addCont('Has rechazado la solicitud de acceso.');
                $container->do_output();
                return;
            }

            if ($action === 'approve') {
                $this->clearSessionRequest();

                $apitokenMan = $user->man->getApitokenMan();
                if (!$apitokenMan) {
                    $alert = $container->add_cont_elem(false, 'div');
                    $alert->set_att('class', 'alert alert-danger');
                    $alert->addCont('El sistema de tokens no está disponible.');
                    $container->do_output();
                    return;
                }

                // Re-filter scope: only codes the user actually has.
                $cleanScope = [];
                foreach ($scope as $code) {
                    if ($user->man->allow($code)) {
                        $cleanScope[] = $code;
                    }
                }
                if (empty($cleanScope)) {
                    $alert = $container->add_cont_elem(false, 'div');
                    $alert->set_att('class', 'alert alert-danger');
                    $alert->addCont('No tienes ninguno de los permisos solicitados.');
                    $container->do_output();
                    return;
                }

                $result = $apitokenMan->createToken($user->get_id(), $label, $cleanScope);
                if (!$result) {
                    $alert = $container->add_cont_elem(false, 'div');
                    $alert->set_att('class', 'alert alert-danger');
                    $alert->addCont('No se pudo crear el token.');
                    $container->do_output();
                    return;
                }

                $tokenStr = $result['token'];

                // Redirect mode: send token to redirect_uri
                $deliverMode = $_POST['_authz_deliver'] ?? 'redirect';
                if ($deliverMode === 'redirect') {
                    $sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
                    $prefillPerms = implode(',', $cleanScope);
                    $redirectTarget = $redirectUri
                        . $sep . 'token=' . urlencode($tokenStr)
                        . '&_apitk_open_create=1'
                        . '&_apitk_label=' . urlencode($label)
                        . '&_apitk_prefill_perms=' . urlencode($prefillPerms);
                    ob_end_clean();
                    header('Location: ' . $redirectTarget);
                    exit;
                }

                // Manual copy mode — show token + MTC server URL
                $serverRow = $container->add_cont_elem(false, 'p');
                $serverRow->set_att('class', 'text-muted small mb-2');
                $serverRow->addCont('<strong>Servidor MTC:</strong> <code>'
                    . htmlspecialchars($redirectUri) . '</code>');

                $banner = $container->add_cont_elem(false, 'div');
                $banner->set_att('class', 'alert alert-success');
                $banner->addCont('<strong>Token generado.</strong> Cópialo ahora, no se volverá a mostrar.<br>'
                    . '<code class="d-block mt-2 p-2 bg-white border rounded text-break user-select-all" '
                    . 'style="word-break:break-all">'
                    . htmlspecialchars($tokenStr)
                    . '</code>');

                $container->do_output();
                return;
            }
        }

        // --- GET: build consent form ---
        $scopeInfo  = $this->buildScopeInfo($scope, $user);
        $hasWarning = false;
        foreach ($scopeInfo as $info) {
            if ($info['status'] !== 'ok') {
                $hasWarning = true;
            }
        }

        $safeLabel       = htmlspecialchars($label);
        $safeRedirectUri = htmlspecialchars($redirectUri);
        $safeNonce       = htmlspecialchars($nonce);

        $card     = $container->add_cont_elem(false, 'div');
        $card->set_att('class', 'card');
        $cardBody = $card->add_cont_elem(false, 'div');
        $cardBody->set_att('class', 'card-body');

        // Title
        $title = $cardBody->add_cont_elem(false, 'h5');
        $title->set_att('class', 'card-title');
        $title->addCont('<i class="fa fa-key me-2"></i>Solicitud de acceso');

        // MTC server URL
        $serverRow = $cardBody->add_cont_elem(false, 'div');
        $serverRow->set_att('class', 'mb-2 small text-muted');
        $serverRow->addCont('<strong>Servidor MTC:</strong> <code>' . $safeRedirectUri . '</code>');

        // App label
        $info = $cardBody->add_cont_elem(false, 'p');
        $info->set_att('class', 'text-muted small mb-3');
        $info->addCont('<strong>' . $safeLabel . '</strong> solicita acceso a tu cuenta.');

        // Warning if any scope is not available
        if ($hasWarning) {
            $w = $cardBody->add_cont_elem(false, 'div');
            $w->set_att('class', 'alert alert-warning small');
            $w->addCont('<i class="fa fa-exclamation-triangle me-1"></i>'
                . 'Algunos permisos solicitados no están disponibles para tu cuenta. '
                . 'El token se creará solo con los permisos que sí tienes.');
        }

        // Scope list
        $listTitle = $cardBody->add_cont_elem(false, 'p');
        $listTitle->set_att('class', 'fw-bold mb-1');
        $listTitle->addCont('Permisos solicitados:');

        $ul = $cardBody->add_cont_elem(false, 'ul');
        $ul->set_att('class', 'list-group mb-3');
        foreach ($scopeInfo as $info) {
            $safeCode = htmlspecialchars($info['code']);
            $li = $ul->add_cont_elem(false, 'li');
            $li->set_att('class', 'list-group-item d-flex align-items-center gap-2');
            switch ($info['status']) {
                case 'ok':
                    $li->addCont('<i class="fa fa-check-circle text-success"></i> <code>' . $safeCode . '</code>');
                    break;
                case 'warning':
                    $li->addCont('<i class="fa fa-times-circle text-danger"></i> <code>' . $safeCode . '</code>'
                        . ' <span class="text-danger small">(no disponible para tu cuenta)</span>');
                    break;
                case 'unknown':
                    $li->addCont('<i class="fa fa-question-circle text-warning"></i> <code>' . $safeCode . '</code>'
                        . ' <span class="text-warning small">(permiso desconocido)</span>');
                    break;
            }
        }

        // Form
        $form = $cardBody->add_cont_elem(false, 'form');
        $form->set_att('method', 'post');

        $nonceInput = $form->add_cont_elem(false, 'input');
        $nonceInput->set_att('type', 'hidden');
        $nonceInput->set_att('name', '_authz_nonce');
        $nonceInput->set_att('value', $nonce);

        // Deliver mode selector
        $deliverWrap = $form->add_cont_elem(false, 'div');
        $deliverWrap->set_att('class', 'mb-3 small');

        $optRedirect = $deliverWrap->add_cont_elem(false, 'div');
        $optRedirect->set_att('class', 'form-check form-check-inline');
        $optRedirect->addCont('<input class="form-check-input" type="radio" name="_authz_deliver" '
            . 'id="_authz_redirect" value="redirect" checked>'
            . '<label class="form-check-label" for="_authz_redirect">'
            . 'Enviar token automáticamente a <code>' . $safeRedirectUri . '</code></label>');

        $optCopy = $deliverWrap->add_cont_elem(false, 'div');
        $optCopy->set_att('class', 'form-check form-check-inline');
        $optCopy->addCont('<input class="form-check-input" type="radio" name="_authz_deliver" '
            . 'id="_authz_copy" value="copy">'
            . '<label class="form-check-label" for="_authz_copy">Mostrar token para copiar manualmente</label>');

        // Redirect warning
        $redirectWarn = $form->add_cont_elem(false, 'div');
        $redirectWarn->set_att('id', '_authz_redirect_warn');
        $redirectWarn->set_att('class', 'alert alert-warning small mb-3');
        $redirectWarn->addCont('<i class="fa fa-exclamation-triangle me-1"></i>'
            . 'Al autorizar, tu token de acceso se enviará directamente a: <strong><code>'
            . $safeRedirectUri . '</code></strong>. Asegúrate de confiar en esta dirección.');

        // Buttons
        $btns = $form->add_cont_elem(false, 'div');
        $btns->set_att('class', 'd-flex gap-2');

        $btnApprove = $btns->add_cont_elem(false, 'button');
        $btnApprove->set_att('type', 'submit');
        $btnApprove->set_att('name', '_authz_action');
        $btnApprove->set_att('value', 'approve');
        $btnApprove->set_att('class', 'btn btn-primary');
        $btnApprove->addCont('<i class="fa fa-check me-1"></i>Autorizar');

        $btnDeny = $btns->add_cont_elem(false, 'button');
        $btnDeny->set_att('type', 'submit');
        $btnDeny->set_att('name', '_authz_action');
        $btnDeny->set_att('value', 'deny');
        $btnDeny->set_att('class', 'btn btn-outline-secondary');
        $btnDeny->addCont('<i class="fa fa-times me-1"></i>Rechazar');

        // JS to toggle warning visibility
        $script = $cardBody->add_cont_elem(false, 'script');
        $script->addCont('(function(){'
            . 'var w=document.getElementById("_authz_redirect_warn");'
            . 'document.querySelectorAll("input[name=\'_authz_deliver\']").forEach(function(r){'
            . 'r.addEventListener("change",function(){'
            . 'w.style.display=r.value==="redirect"?"":"none";'
            . '});});'
            . '})();');

        $container->do_output();
    }
}
?>
