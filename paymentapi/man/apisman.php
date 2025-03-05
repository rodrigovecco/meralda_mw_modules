<?php
class  mwmod_mw_paymentapi_man_apisman extends mwmod_mw_paymentapi_man_abs{
	function __construct(){
		$this->init();	
	}
	function get_debug_data(){
		$r=array(
			"includeProduction"=>$this->includeProduction,
			"includeDebug"=>$this->includeDebug,
			"onlyEnabled"=>$this->onlyEnabled,
			"productionManCod"=>$this->productionManCod,
			"debugManCod"=>$this->debugManCod,
			"class"=>get_class($this),
			"items"=>$this->get_debug_data_items(),
		
		);
		return $r;	
	}
	function get_debug_data_items(){
		$r=array();
		if($items=$this->get_items()){
			foreach($items as $id=>$item){
				$r[$id]=$item->get_debug_data();	
			}
		}
		return $r;	
		
	}

}
?>