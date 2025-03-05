<?php
class mwmod_mw_placeholder_processor_phprocessor extends mw_apsubbaseobj{
	private $cod="def";
	private $matrix;
	private $_txt;//proc txt
	private $ph_src;//
	
	var $debug_nl2br=false;
	
	
	
	
	function __construct($cod="def"){
		if($cod){
			$this->set_cod($cod);	
		}
	}
	final function get_text_final(){
		$this->proc_if_needed();
		return $this->_txt;	
	}
	final function proc_if_needed(){
		if(isset($this->_txt)){
			return false;	
		}
		$this->_txt="";
		if($this->matrix){
			if($t=$this->matrix->get_text_for_ph_src($this->ph_src)){
				$this->_txt=$t;
				return true;	
			}
		}
		//$this->_txt
	}
	
	function add_ph_sub_src($src){
		if($root=$this->get_or_create_ph_src()){
			return $root->add_item_by_cod($src);	
		}
	}
	function get_or_create_ph_src(){
		if($this->ph_src){
			return $this->ph_src;	
		}
		$src=new mwmod_mw_placeholder_src_root();
		return $this->set_ph_src($src);
	}
	
	final function set_ph_src($src){
		$this->reset_proc();
		$this->ph_src=$src;
		return $this->ph_src;
			
	}
	final function reset_proc(){
		unset($this->_txt);	
	}
	final function set_matrix($matrix){
		$this->reset_proc();
		if($matrix){
			if(is_object($matrix)){
				if(is_a($matrix,"mwmod_mw_placeholder_matrix_phmatrix")){
					$this->matrix=$matrix;
					return true;
	
				}
			}
				
		}
		
	}
	final function set_cod($cod){
		if($cod){
			$this->cod=$cod;
		}
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_ph_src(){
		return $this->ph_src; 	
	}
	final function __get_priv_matrix(){
		return $this->matrix; 	
	}
	
	function get_debug_data_for_mail(){
		$r=array();
		$r["txt"]=$this->get_text_final();
		if($this->debug_nl2br){
			$r["txt"]=nl2br($r["txt"]);	
		}
		if($this->matrix){
			//$r["matrix"]=$this->matrix->get_full_debug_data();
		}
		return $r;
	}
	
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		if($this->matrix){
			$r["matrix"]=$this->matrix->get_full_debug_data();
		}
		return $r;
	}

	
}
?>