<?php
/**
 * Fachada compatible con \Culqi\Culqi del SDK oficial.
 *
 * Expone los recursos como propiedades públicas, con la misma forma de uso:
 *   $culqi = new mwmod_mw_paymentapi_api_culqi_facade($api_key);
 *   $charge = $culqi->Charges->create($params);
 *
 * Implementación sin dependencias externas: usa cURL a través de
 * mwmod_mw_paymentapi_api_culqi_apiclient.
 *
 * @property-read string $api_key
 */
class mwmod_mw_paymentapi_api_culqi_facade {
	public $api_key;
	public $Charges;
	public $Cards;
	public $Customers;
	public $Refunds;
	public $Tokens;
	public $Plans;
	public $Subscriptions;
	public $Iins;
	public $Events;
	public $Orders;
	public $Transfers;

	function __construct($apiKey) {
		$this->api_key = $apiKey;
		$api = new mwmod_mw_paymentapi_api_culqi_apiclient($apiKey);
		$this->Charges       = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/charges");
		$this->Cards         = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/cards");
		$this->Customers     = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/customers");
		$this->Refunds       = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/refunds");
		$this->Tokens        = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/tokens");
		$this->Plans         = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/plans");
		$this->Subscriptions = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/subscriptions");
		$this->Iins          = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/iins");
		$this->Events        = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/events");
		$this->Orders        = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/orders");
		$this->Transfers     = new mwmod_mw_paymentapi_api_culqi_facaderesource($api, "/transfers");
	}
}
?>
