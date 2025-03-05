<?php
class mwmod_mw_devextreme_chart_serie_serie extends mwmod_mw_devextreme_chart_serie_abs{
	
	
	function __construct($cod,$name=false,$type=false,$objclass="mw_devextreme_chart_serie"){
		$this->init_serie($cod,$objclass);
		if($name){
			$this->set_prop("options.name",$name);	
		}
		if($type){
			$this->set_prop("options.type",$type);	
		}
		$this->set_prop("options.valueField",$cod);
		
		
	}
	
	
}
?>