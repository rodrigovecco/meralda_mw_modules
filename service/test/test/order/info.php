<?php
class  mwmod_mw_service_test_test_order_info extends mwmod_mw_service_test_test_abs{
	public $orderId;
	function __construct($id){
		$this->initAsChild($id);
		$this->orderId=$id;
	}
	function createChildByMethod_export($cod){
		return new mwmod_mw_service_test_test_order_export();
	}
	

	function doExecOk($path=false){
		$info=$this->getOrderData();
		$this->outputJSON($info);

		
	}
	function getOrderData(){
		$r=array(
			"id"=>$this->orderId,
			"data"=>array(
				"ddd"=>"xxx",
			),
			"items"=>array(
				array("id"=>1),
				array("id"=>2),
				array("id"=>3),
				array("id"=>4),
				
			)
			
		);
		return $r;
			
	}


	
}
?>