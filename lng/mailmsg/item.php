<?php
class  mwmod_mw_lng_mailmsg_item extends mw_apsubbaseobj{
	private $cod;
	private $man;//mwmod_mw_lng_mailmsg_man
	
	private $_subject;
	private $_plain;
	private $_html;
	
	private $imgsPathMan;
	
	
	function __construct($cod,$man){
		$this->init($cod,$man);	
	}
	final function __get_priv_imgsPathMan(){
		if(!isset($this->imgsPathMan)){
			$this->imgsPathMan=$this->loadImgsPathMan();	
		}
		
		return $this->imgsPathMan; 	
	}
	
	function loadImgsPathMan(){
		if(!$subpath=$this->get_files_subpath()){
			return false;
		}
		$subpath.="/img";
		return $this->mainap->get_sub_path_man($subpath,"instance");
		
			
		
	}
	function getImgsPathMan(){
		return $this->__get_priv_imgsPathMan();
			
		
	}
	
	////
	function new_ph_processors_gr(){
		$o=new mwmod_mw_placeholder_processor_phprocessorgroup();
		$item=$o->new_item("subject");
		$item->set_matrix($this->get_subject());
		$item=$o->new_item("plain");
		$item->set_matrix($this->get_plain());
		$item=$o->new_item("html");
		$item->set_matrix($this->get_html());
		return $o;
			
	}
	function load_plain(){
		return $this->load_ph_matrix_from_first_file("plain.txt");	
	}
	final function get_plain(){
		if(!isset($this->_plain)){
			$this->_plain=$this->load_plain();
		}
		return $this->_plain;
	}
	function load_html(){
		return $this->load_ph_matrix_from_first_file("html.html");	
	}
	final function get_html(){
		if(!isset($this->_html)){
			$this->_html=$this->load_html();
		}
		return $this->_html;
	}
	
	function load_subject(){
		return $this->load_ph_matrix_from_first_file("subject.txt");	
	}
	final function get_subject(){
		if(!isset($this->_subject)){
			$this->_subject=$this->load_subject();
		}
		return $this->_subject;
	}
	function load_ph_matrix_from_first_file($file){
		$matrix=new mwmod_mw_placeholder_matrix_phmatrix();
		if(!$list=$this->get_file_posible_locations($file)){
			return $matrix;	
		}
		$matrix->set_debug_info("posible_files",$list);
		foreach($list as $cod=>$f){
			if($f){
				if(is_file($f)){
					if(file_exists($f)){
						$matrix->set_debug_info("src_file",$f);
						$matrix->set_text(file_get_contents($f));
						return $matrix;
					}
				}
			}
		}
		return $matrix;		
	}
	
	function get_debug_data(){
		$r=array();
		if($pggr=$this->new_ph_processors_gr()){
			$r["phprocessor"]=$pggr->get_debug_data();	
		}
		return $r;
			
	}
	function get_files_locations_info(){
		$r=array();
		$r["files"]=array(
			"subject"=>$this->get_file_posible_locations("subject.txt"),
			"plain"=>$this->get_file_posible_locations("plain.txt"),
			"html"=>$this->get_file_posible_locations("html.html"),
		);
		return $r;
			
	}
	function get_current_lng_cod(){
		if($man=$this->man->lngman){
			if($lng=$man->get_current_lng_man()){
				return basename($lng->code);	
			}
		}
	}
	function get_file_posible_locations($file){
		if(!$file=basename($file)){
			return false;
		}
		if(!$subpath=$this->get_files_subpath()){
			return false;
		}
		$lngcod=$this->get_current_lng_cod();
		$r=array();
		if($lngcod){
			if($p=$this->mainap->get_sub_path($subpath."/$lngcod","instance")){
				$r["instance_lng"]=$p."/".$file;	
			}
		}
		if($p=$this->mainap->get_sub_path($subpath,"instance")){
			$r["instance_def"]=$p."/".$file;	
		}
		if($lngcod){
			if($p=$this->mainap->get_sub_path($subpath."/$lngcod","system")){
				$r["system_lng"]=$p."/".$file;	
			}
		}
		if($p=$this->mainap->get_sub_path($subpath,"system")){
			$r["system_def"]=$p."/".$file;	
		}
		return $r;
		
	}
	
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	
	function get_files_subpath(){
		return $this->man->get_item_files_subpath($this);
		if(!$c=basename($this->cod)){
			return false;	
		}
		return "mailmsgs/$c";	
	}

	final function init($cod,$man){
		$this->man=$man;
		$this->cod=$cod;
		
	}

}
?>