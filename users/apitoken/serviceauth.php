<?php
/**
 * API Token Service Auth Helper
 * Authenticates a service request using a Bearer token from the Authorization header.
 *
 * Usage (in a service endpoint):
 *
 *   $user = mwmod_mw_users_apitoken_serviceauth::authenticateRequest($mainap);
 *   if (!$user) {
 *       http_response_code(401);
 *       echo json_encode(["ok" => false, "error" => "unauthorized"]);
 *       exit;
 *   }
 *
 * After this call, $mainap->usersMan->getCurrentApiToken() returns the active token,
 * and all subsequent allow() calls will double-check against token permissions.
 */
class mwmod_mw_users_apitoken_serviceauth {

	/**
	 * Extract Bearer token, validate it, load the user, and bind the token to the
	 * users manager so that permission checks apply the token scope restriction.
	 *
	 * @param mw_application $mainap
	 * @return mwmod_mw_users_user|false  The authenticated user, or false on failure
	 */
	static function authenticateRequest($mainap) {
		$rawToken = self::extractBearerToken();
		if (!$rawToken) {
			return false;
		}

		$usersMan = $mainap->get_submanager("users");
		if (!$usersMan) {
			return false;
		}

		// Load the apitoken manager (lazy, attached to users man)
		$apitokenMan = $usersMan->getApitokenMan();
		if (!$apitokenMan) {
			return false;
		}

		$tokenItem = $apitokenMan->findActiveByRawToken($rawToken);
		if (!$tokenItem) {
			return false;
		}

		// Load the associated user
		$user = $usersMan->get_item($tokenItem->getUserId());
		if (!$user || !$user->is_active()) {
			return false;
		}

		// Bind token and user to the manager
		$usersMan->set_currentuser_obj($user);
		$usersMan->setCurrentApiToken($tokenItem);

		return $user;
	}

	/**
	 * Extract the raw token from the Authorization: Bearer <token> header.
	 * @return string|false
	 */
	static function extractBearerToken() {
		$header = "";

		if (!empty($_SERVER["HTTP_AUTHORIZATION"])) {
			$header = $_SERVER["HTTP_AUTHORIZATION"];
		} elseif (function_exists("apache_request_headers")) {
			$headers = apache_request_headers();
			$header  = $headers["Authorization"] ?? "";
		}

		if (!$header) {
			return false;
		}

		if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
			return false;
		}

		return trim($m[1]);
	}
}
?>
