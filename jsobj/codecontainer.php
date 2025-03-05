<?php
class mwmod_mw_jsobj_codecontainer extends mwmod_mw_jsobj_obj{
	private $_cont=array();
	public $contSeparator="";
	
	
	private $_vardeclarations=array();
	
	function __construct($jscode=false){
		$this->add_cont($jscode);
	}
	function addCont(){
		if($args=func_get_args()){
			foreach($args as $a){
				$continue=true;
				if(is_string($a)){
					if(substr($a,0,3)==="[T]"){
						$a="".$this->get_txt(substr($a,3))."";	
					}
				}
				if($continue){
					$this->add_cont($a);	
				}
			}
		}
	}
	function addVarDeclaration($name,$value,$ontop=false){
		$vc=new mwmod_mw_jsobj_vardeclaration($name,$value);
		$this->add_cont($vc);
		$this->_addVarDeclaration($name,$vc);
		return $vc;
	}
	final function _addVarDeclaration($cod,$obj){
		$this->_vardeclarations[$cod]=$obj;	
	}
	
	final function add_cont($cont,$ontop=false){
		if($cont){
			if($ontop){
				$list=$this->_cont;
				$this->_cont=array();
				$this->_cont[]=$cont;
				foreach($list as $c){
					$this->_cont[]=$c;
				}
			}else{
				$this->_cont[]=$cont;
			}
			return $cont;
		}
		return false;	
	}
	
	final function get_cont_list(){
		return $this->_cont;	
	}
	function get_as_js_val_open(){
		return "";	
	}
	function get_as_js_val_close(){
		return "";	
	}
	function get_js_code_in(){
		if(!$list=$this->get_cont_list()){
			return "";	
		}
		$r=array();
		foreach($list as $e){
			if(is_object($e)){
				if(is_a($e,"mwmod_mw_jsobj_obj")){
					$r[]=$e->get_as_js_val();	
				}
			}else{
				if(is_string($e)){
					$r[]=$e;	
				}
			}
		}
		return implode($this->contSeparator,$r);
	}
	function get_as_js_val(){
		$r=$this->get_as_js_val_open();
		$r.=$this->get_js_code_in();
		$r.=$this->get_as_js_val_close();
		return $r;
		
		//return $this->get_js_fnc_code_in();
	}
}
?>