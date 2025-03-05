<?php
abstract class mwmod_mw_modulesman_mainmanabs extends mw_apsubbaseobj{
	private $_dirmanagers=array();//list of modules directories
	private $autoloadman;
	private $helper;
	
	private $def_dir_managers_created=false;
	function get_autoload_dirman_for_class($class){
		return false;
	}
	function get_csv_data(){
		if(!$pm=$this->get_info_path_man()){
			return false;	
		}
		if(!$path=$pm->check_and_create_path()){
			return false;	
		}
		//echo $path."<br>";
		if(!$pm->file_exists("info.csv")){
			return false;
		}
		if(!$h=fopen($path."/info.csv", 'r')){
			return false;	
		}
		$row = -1;
		$cods=array(
		"manager","mode","cod","type","version","path"
		);
		$r=array();
		while (($data = fgetcsv($h, 1000, ";")) !== FALSE) {
			if($row>0){
				reset($cods);
				$d=array();
				$x=0;
				foreach($cods as $c){
					$d[$c]=$data[$x];
					$d["index"]=$row;
					$x++;
				}
				$r[$row]=$d;
			}
			$row++;
			
		}
			
		fclose($h);
		return $r;
			
	}
	function update_csv(){
		if(!$pm=$this->get_info_path_man()){
			return false;	
		}
		if(!$path=$pm->check_and_create_path()){
			return false;	
		}
		//echo $path."<br>";
		if($pm->file_exists("info.csv")){
			unlink($path."/info.csv");
			//echo "existe<br>";
		}
		$h=fopen($path."/info.csv", 'a');
		fwrite($h,"sep=;\n");
		$data=array(
		"manager","mode","cod","type","version","path"
		);
		fputcsv($h,$data,";");
		if($items=$this->get_all_info_managers()){
			foreach($items as $item){
				$data=array(
					"manager"=>$item->dirman->cod,
					"mode"=>$item->dirman->mode,
					"cod"=>$item->cod,
					"type"=>$item->type,
					"version"=>$item->get_data("version"),
					"path"=>$item->get_rel_path(),
				);
				fputcsv($h,$data,";");	
			}
		}
		
		fclose($h);
		return true;

		
		//$file=
		//$file=$this->maina	
	}
	function get_info_path_man(){
		if($pm=$this->mainap->get_sub_path_man("mwmodulesinfo","root")){
			return $pm;
		}
		
	}

	function get_all_info_managers(){
		$r=array();
		if($mans=$this->get_dirmans()){
			foreach($mans as $mcod=>$man){
				if($items=$man->get_info_managers()){
					foreach($items as $cod=>$item){
						$r[$mcod."/".$cod]=$item;	
					}
				}
			}
		}
		return $r;
	}
	function new_autoload_dir_man($subprefman){
		if(!$cod=$this->get_dirman_cod_for_autoload_subprefman($subprefman)){
			return false;	
		}
		if(!$fullpath=$subprefman->basepath){
			return false;	
		}
		$syspath=$this->mainap->get_path("system");
		$len=strlen($syspath);
		if(substr($fullpath,0,$len)!=$syspath){
			return false;	
		}
		$subpath=trim(substr($fullpath,$len),"/");
		$dirman=new mwmod_mw_modulesman_dirman_autoload($cod,$subpath,$subprefman);
		return $dirman;
	}
	function get_dirman_cod_for_autoload_subprefman($subprefman){
		return 	"autoload_".$subprefman->prefman->pref."_".$subprefman->pref;
	}
	function get_dirs_mans_debug_data(){
		$r=array();
		if($items=$this->get_dirmans()){
			foreach($items as $cod=>$item){
				$r[$cod]=$item->get_debug_data();	
			}
		}
		return $r;
	}
	final function get_dirman($cod){
		$this->init_dir_managers();
		if($cod){
			return $this->_dirmanagers[$cod]??null;	
		}
	}
	final function get_dirmans(){
		$this->init_dir_managers();
		return $this->_dirmanagers;	
	}
	final function init_dir_managers(){
		$this->init_def_dir_managers();	
	}
	function create_def_dir_managers(){
		//	
	}
	final function init_def_dir_managers(){
		if($this->def_dir_managers_created){
			return;	
		}
		$this->create_def_dir_managers();
		$this->def_dir_managers_created=true;
	}
	final function __get_def_dir_managers_created(){
		return $this->def_dir_managers_created; 	
	}
	
	final function __get_priv_autoloadman(){
		if(!isset($this->autoloadman)){
			$this->autoloadman=mw_get_autoload_manager();	
		}
		return $this->autoloadman; 	
	}
	final function __get_priv_helper(){
		if(!isset($this->helper)){
			$this->helper=new mwmod_mw_ap_helper();	
		}
		return $this->helper; 	
	}
	
	final function add_dirman($man){
		if(!$cod=$man->cod){
			return false;	
		}
		if($this->_dirmanagers[$cod]??null){
			return false;	
		}
		$this->_dirmanagers[$cod]=$man;
		$man->set_main_man($this);
		return $man;
	}
	
	final function init($ap=false){
		$this->set_mainap($ap);		
	}

	
}
?>