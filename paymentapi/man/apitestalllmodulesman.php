<?php
class  mwmod_mw_paymentapi_man_apitestalllmodulesman extends mwmod_mw_paymentapi_man_apisman{
	function __construct(){
		$this->init();	
	}
	function loadItems(){
		$list=new mwmod_mw_util_itemslist();
		$item=$list->add_item(new mwmod_mw_paymentapi_api_test_man($this));
		$item=$list->add_item(new mwmod_mw_paymentapi_api_culqi_man($this));
		$item=$list->add_item(new mwmod_mw_paymentapi_api_culqi_testman($this));
		$item=$list->add_item(new mwmod_mw_paymentapi_api_niubiz_man($this));
		$item=$list->add_item(new mwmod_mw_paymentapi_api_niubiz_testman($this));
		
		return $list->get_items();
			
	}


}
?>