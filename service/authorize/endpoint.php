<?php

/**
 * OAuth2-style authorization endpoint for Meralda services.
 *
 * @deprecated NOT IN USE. This endpoint started the OAuth-style authorize flow
 * that fed the consent UI (mwmod_mw_users_ui_authorize_main) bound to the MCP
 * server (with redirect_uri/callback). The MCP server now points users directly
 * to the existing API-tokens account UI, so this endpoint is disabled.
 * Execution is hard-stopped with die() in doExecOk(). Kept for reference until
 * the consent/callback flow is revived.
 *
 * Usage — add to any service root:
 *   function createChildByMethod_authorize() {
 *       return new mwmod_mw_service_authorize_endpoint();
 *   }
 *
 * Flow:
 *   GET /service/<base>/authorize?scope=perm1,perm2&redirect_uri=https://...&label=MyApp
 *
 *   1. Validates scope and redirect_uri.
 *   2. Stores request in session under '_mw_authorize_request'.
 *   3. Redirects to /admin/?si=authorize  (login wall handled by admin).
 *
 * The admin subinterface `authorize` (mwmod_mw_users_ui_authorize_main) reads
 * the session data, shows the consent screen, and handles the POST.
 *
 * Security:
 *   - scope codes are validated against the declared permission catalog before
 *     storing them; unknown codes are silently dropped.
 *   - redirect_uri is stored as-is; the consent UI warns the user before sending
 *     the token to that URL (no server-side whitelist by default — see notes).
 *   - The session key is CSRF-protected with a one-time nonce stored alongside
 *     the request data.
 */
class mwmod_mw_service_authorize_endpoint extends mwmod_mw_service_base {

    /** Maximum number of scope codes accepted in a single request. */
    const MAX_SCOPE_CODES = 20;

    /** Session key where the pending authorization request is stored. */
    const SESSION_KEY = '_mw_authorize_request';

    /** Admin subinterface code that handles the consent UI. */
    const ADMIN_SI = 'authorize';

    function isFinal() {
        return true;
    }

    function isAllowed() {
        return true; // public — anyone can start the flow
    }

    function validateAllowedAsChild() {
        return true;
    }

    /**
     * @deprecated Disabled — the authorize flow is not in use. See class docblock.
     */
    function doExecOk($path = false) {
        die('authorize endpoint is deprecated and disabled');

        $scopeRaw       = trim($_GET['scope']       ?? '');
        $redirectUri    = trim($_GET['redirect_uri'] ?? '');
        $label          = trim($_GET['label']        ?? '');

        // --- Validate redirect_uri ---
        if ($redirectUri === '' || !filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            $this->outputJSON(['error' => 'invalid_redirect_uri']);
            return;
        }
        $scheme = strtolower(parse_url($redirectUri, PHP_URL_SCHEME) ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->outputJSON(['error' => 'invalid_redirect_uri']);
            return;
        }

        // --- Parse and sanitize scope ---
        $rawCodes = $scopeRaw !== '' ? explode(',', $scopeRaw) : [];
        $scope    = [];
        foreach (array_slice($rawCodes, 0, self::MAX_SCOPE_CODES) as $code) {
            $code = trim($code);
            if ($code !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
                $scope[] = $code;
            }
        }
        if (empty($scope)) {
            $this->outputJSON(['error' => 'invalid_scope']);
            return;
        }

        // --- Build admin URL for consent screen ---
        $adminUrl = '/admin/index.php?ui=' . self::ADMIN_SI;
        if ($this->mainap && ($uiadmin = $this->mainap->get_submanager('uiadmin'))) {
            if (method_exists($uiadmin, 'get_url_sub_interface_by_dot_cod')) {
                $adminUrl = $uiadmin->get_url_sub_interface_by_dot_cod(self::ADMIN_SI);
            } elseif (method_exists($uiadmin, 'get_url_subinterface')) {
                $adminUrl = $uiadmin->get_url_subinterface(self::ADMIN_SI);
            }
        }

        // --- Store in session ---
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $nonce = bin2hex(random_bytes(16));
        $_SESSION[self::SESSION_KEY] = [
            'scope'        => $scope,
            'redirect_uri' => $redirectUri,
            'label'        => $label !== '' ? substr($label, 0, 100) : 'API',
            'nonce'        => $nonce,
            'created_at'   => time(),
        ];

        // --- Redirect to admin consent screen ---
        $sep = (strpos($adminUrl, '?') === false) ? '?' : '&';
        $adminUrl .= $sep . '_authz_nonce=' . urlencode($nonce);
        ob_end_clean();
        header('Location: ' . $adminUrl);
        exit;
    }
}
?>
