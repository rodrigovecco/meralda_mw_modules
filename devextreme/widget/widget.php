<?php
class mwmod_mw_devextreme_widget_widget extends mw_apsubbaseobj{
	var $container_id="dxwidget";
	private $js_props;
	
	private $htmlelem;
	
	var $formatter;
	private $devextreme_man;
	
	function load_devextreme_man(){
		return $this->mainap->get_submanager("devextreme");
	}
	final function __get_priv_devextreme_man(){
		if(!isset($this->devextreme_man)){
			if(!$this->devextreme_man=$this->load_devextreme_man()){
				$this->devextreme_man=false;	
			}
		}
		return $this->devextreme_man;
	}

	
	function __construct($name="dxwidget"){
		$this->set_container_id($name);
	}
	function create_formatter(){
		if(class_exists("mwcus_cus_templates_devextreme_formatter")){
			return new mwcus_cus_templates_devextreme_formatter();	
		}
		return new mwmod_mw_devextreme_formatter();
	}
	function get_formatter(){
		if(!isset($this->formatter)){
			$this->formatter=$this->create_formatter();	
		}
		return $this->formatter;
	}
	
	function get_js_widget_fnc_name(){
		return "dxDataGrid";	
	}
	
	final function  create_html_elem(){
		$elem=new mwmod_mw_html_elem("div");
		$elem->set_att("id",$this->container_id);
		return $elem;	
	}
	function get_html_container(){
		$e=$this->__get_priv_htmlelem();
		return $e->get_as_html();
		//return "<div id='".$this->container_id."'></div>";	
	}
	function prepare_js_props(){
		$this->__get_priv_js_props();	
	}
	function new_js_doc_ready(){
		$jsin=$this->new_js_create();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc($jsin);
		return $js;
			
	}
	function new_js_create(){
		$this->prepare_js_props();
		$js=new mwmod_mw_jsobj_jquery_action("#".$this->container_id,$this->get_js_widget_fnc_name(),$this->js_props);
		return $js;
	}
	/*
	function js_code(){
			
	}
	function get_html_jsscript_full(){
		$r="";
		$code=$this->js_code();
		return "<script language='javascript' type='text/javascript'>\n$code\n</script>\n";	
	}
	*/
	function set_container_id($name="dxwidget"){
		$this->container_id=$name;
		if($elem=$this->get_htmlelem_if_exists()){
			$elem->set_att("id",$this->container_id);
		}
	}
	final function get_htmlelem_if_exists(){
		if(isset($this->htmlelem)){
			return $this->htmlelem;
		}
	}
	
	final function __get_priv_htmlelem(){
		if(!$this->htmlelem){
			$this->htmlelem=$this->create_html_elem();
		}
		return $this->htmlelem;
	}
	final function __get_priv_js_props(){
		if(!$this->js_props){
			$this->js_props=new mwmod_mw_jsobj_obj();
			$this->setDefaultJSProps($this->js_props);
		}
		return $this->js_props;
	}
	function setDefaultJSProps($js_props){

	}
	
}
?>