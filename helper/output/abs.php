<?php
abstract class mwmod_mw_helper_output_abs extends mw_baseobj{
	private $alert;
	private $js;
	private $xml;
	private $html;
	public $debugMode=false;
	public $alertJSpropcod;
	public $debugModeXML=true;
	public $debugdata=array();
	public $xmlDebugListProp="debug.events";
	public $xmlAlertsListProp="alerts.list";
	
	function __construct(){
	}
	function getDebugLog(){
		if(sizeof($this->debugdata)){
			return $this->debugdata;	
		}
	}

	function addDebugData($data){
		$index=sizeof($this->debugdata)+1;
		$this->debugdata[$index]=$data;
		if($this->debugMode){
			if($this->debugModeXML){
				if($this->xml){
					$this->xml->set_prop($this->xmlDebugListProp.".".$index,$data);	
				}
			}
				
		}
	}
	function setOutputXMLJSAlertMode($xml=false,$js=false,$alert=false){
		$this->setOutputXMLJSMode($xml,$js);
		if(!$alert){
			$alert=$this->createAlert();	
		}
		$this->setAlert($alert);
		$this->alertJSpropcod="notify";
		$this->js->set_prop("notify",$alert->getJsNotify());
		return true;
		
	}
	function sendAlert2HtmlList($dismissible=NULL){
		$xmlprop=$this->xmlAlertsListProp;
		if(!$alert=$this->getAlertAndUnset()){
			return false;	
		}
		if(!is_null($dismissible)){
			$alert->dismissible=$dismissible;	
		}
		if($this->xml){
			if($array=$this->xml->arrayItem($xmlprop)){
				$htmlItem=$array->addHTML($alert);	
			}
			//return false;
		}
		$newalert=$this->createAlert();
		$this->setAlert($newalert);	
		if($this->js){
			if($this->alertJSpropcod){
				$this->js->set_prop($this->alertJSpropcod,$newalert->getJsNotify());	
			}
			
		}
		return $alert;
		
		
		
	}
	function setOutputXMLJSMode($xml=false,$js=false){
		if(!$js){
			$js=$this->createJS();	
		}
		if(!$xml){
			$xml=$this->createXML();	
		}
		$xml_js=new mwmod_mw_data_xml_js("js",$js);
		$xml->add_sub_item($xml_js);
		$this->setXML($xml);
		$this->setJS($js);
		return true;
		
		
	}
	final function setAlert($alert=false){
		if(!$alert){
			$alert=$this->createAlert();	
		}
		$this->alert=$alert;
		return $alert;
		
	}
	function createAlert(){
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert();
		$alert->setMsgSuccess();
		return $alert;
	}
	final function getAlertAndUnset(){
		if(!isset($this->alert)){
			return false;	
		}
		$r=$this->alert;
		unset($this->alert);
		return $r;
	}
	final function __get_priv_alert(){
		if(!isset($this->alert)){
			$this->alert=$this->createAlert();
		}
		return $this->alert;	
	}
	
	final function setJS($js=false){
		if(!$js){
			$js=$this->createJS();	
		}
		$this->js=$js;
		return $js;
		
	}
	function createJS(){
		$js=new mwmod_mw_jsobj_obj();
		return $js;
	}
	
	final function __get_priv_js(){
		if(!isset($this->js)){
			$this->js=$this->createJS();
		}
		return $this->js;	
	}
	final function setXML($xml=false){
		if(!$xml){
			$xml=$this->createXML();	
		}
		$this->xml=$xml;
		return $xml;
		
	}
	function createXML(){
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		return $xml;
	}
	
	final function __get_priv_xml(){
		if(!isset($this->xml)){
			$this->xml=$this->createXML();
		}
		return $this->xml;	
	}
	final function setHTML($html=false){
		if(!$html){
			$html=$this->createHTML();	
		}
		$this->html=$html;
		return $html;
		
	}
	function createHTML(){
		$html=new mwmod_mw_html_cont_varcont();
		
		return $html;
	}
	
	final function __get_priv_html(){
		if(!isset($this->html)){
			$this->html=$this->createHTML();
		}
		return $this->html;	
	}
}
?>