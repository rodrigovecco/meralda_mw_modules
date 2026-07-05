<?php
/**
 * OAuth Client Item.
 *
 * Represents a single row from the `oauth_clients` table.
 *
 * Clients are "public" (RFC 7591, token_endpoint_auth_method = "none"): they
 * have no secret and authenticate at /token solely via the PKCE code_verifier.
 * This is the only mode OAuth 2.1 allows for browser/native clients like
 * Claude Custom Connectors.
 *
 * @extends mwmod_mw_manager_item
 */
class mwmod_mw_oauth_client_item extends mwmod_mw_manager_item {

	/** @var string[]|null Decoded redirect URIs; null until first access. */
	private $redirectUrisCache = null;

	function __construct($tblitem, $man) {
		$this->init($tblitem, $man);
	}

	/** @return string Random hex OAuth client identifier. */
	function getClientId() {
		return (string) $this->get_data('client_id');
	}

	/** @return string Human-readable name shown on the consent screen. */
	function getName() {
		return (string) $this->get_data('client_name');
	}

	/** @return string ISO timestamp. */
	function getCreatedAt() {
		return (string) $this->get_data('created_at');
	}

	/**
	 * Decoded list of registered redirect URIs.
	 *
	 * @return string[]
	 */
	function getRedirectUris() {
		if ($this->redirectUrisCache !== null) {
			return $this->redirectUrisCache;
		}
		$raw = $this->get_data('redirect_uris');
		$decoded = ($raw !== null && $raw !== '') ? json_decode($raw, true) : [];
		$this->redirectUrisCache = is_array($decoded) ? $decoded : [];
		return $this->redirectUrisCache;
	}

	/**
	 * Whether the given redirect_uri is registered for this client.
	 *
	 * Exact string match is required (no wildcard, per OAuth 2.1).
	 *
	 * @param string $uri
	 * @return bool
	 */
	function allowsRedirectUri($uri) {
		if (!is_string($uri) || $uri === '') {
			return false;
		}
		return in_array($uri, $this->getRedirectUris(), true);
	}
}
