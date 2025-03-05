<?php
abstract class mwmod_mw_modulesman_infoman_abs extends mw_apsubbaseobj{
	private $cod;
	private $dirman;
	private $subpath;
	private $filename;
	private $data;
	var $type;
	
	final function get_data($cod=false){
		$this->init_data();
		if($cod){
			return $this->data[$cod];	
		}
		return $this->data;	
	}
	
	function get_rel_path(){
		$r=$this->dirman->subpath;
		$r.="/".$this->subpath;
		if($this->filename){
			$r.="/".$this->filename;	
		}
		return $r;
	}
	
	function check_path(){
		if(!$p=$this->get_info_file_path()){
			return false;	
		}
		
		if(is_dir($p)){
			return true;	
		}
	}
	function get_data_inputs(){
		$gr=new mwmod_mw_datafield_group("data");
		$i=$gr->add_item(new mwmod_mw_datafield_input("name","Name"));	
		$i=$gr->add_item(new mwmod_mw_datafield_number("version","Version"));	
		$i=$gr->add_item(new mwmod_mw_datafield_date("date","Date"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("description","Description"));	
		$i=$gr->add_item(new mwmod_mw_datafield_input("author","Author"));	
		$i=$gr->add_item(new mwmod_mw_datafield_input("web","Web"));	
		$i=$gr->add_item(new mwmod_mw_datafield_input("email","Email"));
		$gr->set_value($this->get_data());
		return $gr;	
			
	}
	function save_data($nd){
		if(!is_array($nd)){
			return false;
		}
		if(!$nd["version"]=$nd["version"]+0){
			$nd["version"]=1;	
		}
		if(!$nd["date"]){
			$nd["date"]=date("Y-m-d H:i:s");
		}
		$this->set_data($nd);
		if(!$this->check_path()){
			return false;	
		}
		if(!$file=$this->get_info_file_full_path()){
			return false;	
		}
		$handle = @fopen($file, "w");
		if (!$handle) {
			return false;	
		}
		foreach($nd as $cod=>$d){
			if($cod=trim($cod)){
				if($d=trim($d)){
					fwrite($handle, $cod." ".$d."\n");	
				}
			}
		}
		fclose($handle);
		return true;
		
	}
	final function set_data($nd){
		if(!is_array($nd)){
			return false;
		}
		$this->data=$nd;
		
	}
	function load_data(){
		if(!$file=$this->get_info_file_full_path()){
			return false;	
		}
		if(!file_exists($file)){
			return false;
		}
		$handle = @fopen($file, "r");
		if (!$handle) {
			return false;	
		}
		$r=array();
		while (($buffer = fgets($handle, 4096)) !== false) {
			if($buffer=trim($buffer)){
				$a=explode(" ",$buffer,2);
				if($a[0]){
					$r[$a[0]]=trim($a[1]);	
				}
			}
			
		}
		fclose($handle);
		return $r;
		

	}
	final function init_data(){
		if(isset($this->data)){
			return;	
		}
		$this->data=array();
		if($d=$this->load_data()){
			$this->data=$d;	
		}
		
	}
	function get_debug_full_data(){
		$r=$this->get_debug_data();
		$r["data"]=$this->get_data();
		return $r;	
	}
	function get_debug_data(){
		$r=array();
		$r["cod"]=$this->cod;	
		$r["subpath"]=$this->subpath;	
		$r["filename"]=$this->filename;	
		$r["type"]=$this->type;	
		$r["infofile"]=$this->get_info_file_full_path();
		return $r;	
	}
	function get_info_file_name(){
		return false;	
	}
	function get_info_file_full_path(){
		if(!$file=$this->get_info_file_name()){
			return false;	
		}
		if(!$p=$this->get_info_file_path()){
			return false;	
		}
		return $p."/".$file;
		
	}
	function get_info_file_path(){
		if(!$this->subpath){
			return false;	
		}
		return $this->dirman->get_info_file_path($this->subpath);
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_dirman(){
		return $this->dirman; 	
	}
	final function __get_priv_subpath(){
		return $this->subpath; 	
	}
	final function __get_priv_filename(){
		return $this->filename; 	
	}

	final function init($cod,$dirman,$subpath,$filename=false){
		$this->cod=$cod;
		$this->subpath=$subpath;
		$this->dirman=$dirman;
		$this->filename=$filename;
	}

	
}
?>