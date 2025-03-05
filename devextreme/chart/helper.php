<?php
class mwmod_mw_devextreme_chart_helper extends mwmod_mw_jsobj_newobject{
	
	private $series;
	private $optimDataSrc;
	function __construct($objclass="mw_devextreme_chart_man"){
		$this->set_fnc_name($objclass);
		/*
		$this->set_container_id($name);
		$this->js_props->set_prop("columnAutoWidth",true);
		$this->js_props->set_prop("allowColumnResizing",true);
		
		*/
		
		
		//$this->columsn=new 
	}
	function add_data($data,$auto_create_fields="ifnone"){	
		$this->__get_priv_optimDataSrc();
		return $this->optimDataSrc->add_data($data,$auto_create_fields);
		
	}
	function add_serie_by_cod($cod,$name=false,$type=false,$objclass="mw_devextreme_chart_serie"){
		$serie=new mwmod_mw_devextreme_chart_serie_serie($cod,$name,$type,$objclass);
		return $this->add_serie($serie);
	}
	function add_serie($jsserie){
		if(!$cod=$jsserie->cod){
			return false;	
		}
		$this->__get_priv_series();
		$this->series->add_data_with_cod($cod,$jsserie);
		return $jsserie;
	}
	final function __get_priv_series(){
		if(!isset($this->series)){
			$this->series=$this->get_array_prop("series");
		}
		return $this->series;
	}
	function set_key($cod="id"){
		$this->__get_priv_optimDataSrc();
		$this->optimDataSrc->set_key($cod);
		$this->set_prop("chartoptions.commonSeriesSettings.argumentField",$cod);
		
	}
	final function __get_priv_optimDataSrc(){
		if(!isset($this->optimDataSrc)){
			$this->optimDataSrc=new mwmod_mw_jsobj_dataoptim();
			$this->set_prop("dsoptim",$this->optimDataSrc);
			//$this->get_array_prop("series");
		}
		return $this->optimDataSrc;
	}
	
}
?>