<?php
class mwmod_mw_placeholder_matrix_phmatrix extends mw_apsubbaseobj{
	private $_original_txt="";
	private $_debug_info=array();
	private $_txt_elems;
	var $ph_open="[[";
	var $ph_close="]]";
	
	
	
	
	function __construct($txt=false){
		if($txt){
			$this->set_text($txt);	
		}
	}
	function create_txt_elems(){
		$result=array();
		$conproc=$this->get_original_txt()."";
		if(!$conproc){
			return $result;	
		}
		$pos=strpos($conproc,$this->ph_open);
		if($pos===false){
			$elem=new mwmod_mw_placeholder_matrix_elem_txt($conproc,$this);
			$result[]=$elem;
			return $result;	
		}
		while (strlen($conproc)){
			$pos=strpos($conproc,$this->ph_open);
			if($pos!==false){
				if($pos>0){
					$txt=substr($conproc,0,$pos);
					$elem=new mwmod_mw_placeholder_matrix_elem_txt($txt,$this);
					$result[]=$elem;
					$conproc=substr($conproc,$pos);
					$pos=0;
				}
				if($posclose=strpos($conproc,$this->ph_close)){
					$txt=substr($conproc,(strlen($this->ph_open)),($posclose-strlen($this->ph_close)));
					$elem=new mwmod_mw_placeholder_matrix_elem_ph($txt,$this);
					$result[]=$elem;
					$conproc=substr($conproc,$posclose+strlen($this->ph_close));
					
				}else{
					$conproc=substr($conproc,strlen($this->ph_open));
				}
			}else{
				if(strlen($conproc)){
					$elem=new mwmod_mw_placeholder_matrix_elem_txt($conproc,$this);
					$result[]=$elem;
					$conproc="";
				}
			}
			
		}
		return $result;
	}
	
	
	function get_text_for_ph_src($src=false){
		$r="";
		if($items=$this->get_txt_elems()){
			foreach($items as $item){
				$r.=$item->get_text_for_ph_src($src);	
			}
		}
		return $r;
	}
	function get_full_debug_data(){
		$r=array();
		$r["original_txt"]=$this->get_original_txt();
		$r["txtproc"]=$this->get_text_for_ph_src();
		$r["info"]=$this->get_debug_info();
		$r["txtelems"]=$this->get_get_txt_elems_debug_data();
		return $r;
	}
	function get_get_txt_elems_debug_data(){
		$r=array();
		$x=0;
		if($items=$this->get_txt_elems()){
			foreach($items as $item){
				$x++;
				$r[$x]=$item->get_debug_data();
				//$r.=$item->get_text_for_ph_src($src);	
			}
		}
		return $r;
			
	}
	final function set_debug_info($cod,$val){
		if($cod){
			mw_array_set_sub_key($cod,$val,$this->_debug_info);
			return true;
		}
	}
	final function get_debug_info($cod=false){
		if($cod){
			return mw_array_get_sub_key($this->_debug_info,$cod);
		}
		return $this->_debug_info;
	}
	final function reset_proc(){
		unset($this->_txt_elems);
			
	}
	final function get_txt_elems(){
		if(isset($this->_txt_elems)){
			return 	$this->_txt_elems;
		}
		$this->_txt_elems=array();
		if($elems=$this->create_txt_elems()){
			$this->_txt_elems=$elems;
		}
		return 	$this->_txt_elems;
		
	}
	final function get_original_txt(){
		return $this->_original_txt;	
	}
	final function set_text($txt=false){
		$this->reset_proc();
		$this->_original_txt="";
		if($txt){
			$txt=$txt."";
			if(is_string($txt)){
				$this->_original_txt=$txt;
				return true;
			}
		}
		
		
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["origtxt"]=$this->get_original_txt();
		$r["info"]=$this->get_debug_info();
		return $r;
	}
	
}
?>