<?php
/**
 * Recurso de la fachada Culqi (Charges, Cards, Customers, Refunds, ...).
 *
 * Implementa los métodos públicos que el código del proyecto usa, manteniendo
 * la misma forma de invocación que el SDK oficial:
 *   $culqi->Charges->create($params)
 *   $culqi->Cards->create($params)
 *   $culqi->Customers->get($id)
 *   etc.
 *
 * Cada instancia se ata a un endpoint base (p.ej. "/charges") y delega en
 * mwmod_mw_paymentapi_api_culqi_apiclient.
 */
class mwmod_mw_paymentapi_api_culqi_facaderesource {
	private $api;
	private $base;

	function __construct($api, $base) {
		$this->api = $api;
		$this->base = $base;
	}
	function create($params, $encryption_params = array(), $custom_headers = null) {
		return $this->api->request("POST", $this->base, $params);
	}
	function get($id) {
		return $this->api->request("GET", $this->base . "/" . $id);
	}
	function update($id, $params = null, $encryption_params = array()) {
		return $this->api->request("PATCH", $this->base . "/" . $id, $params);
	}
	function delete($id) {
		return $this->api->request("DELETE", $this->base . "/" . $id);
	}
	function all($params = array()) {
		return $this->api->request("GET", $this->base, $params);
	}
	// Charges-only
	function capture($id) {
		return $this->api->request("POST", $this->base . "/" . $id . "/capture");
	}
	// Alias para compat con la API antigua del SDK.
	function getCapture($id) {
		return $this->capture($id);
	}
	function getList($params = array()) {
		return $this->all($params);
	}
}
?>
