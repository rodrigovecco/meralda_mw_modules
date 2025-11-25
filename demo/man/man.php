<?php
class mwmod_mw_demo_man_man  extends mwmod_mw_manager_baseman{
	function __construct($mainAP){
		$this->init("demo",$mainAP);
		$this->enable_jsondata();
		$this->enable_strdata();
			
	}
	/**
	 * @param bool $subpath 
	 * @param bool $public 
	 * @return false|mwmod_mw_ap_paths_subpath 
	 */
	function get_sub_path_man($subpath=false,$public=false){
		//nueva
		if($public){
			$mode="userfilespublic";		
		}else{
			$mode="userfiles";	
		}
		if(!$p=$this->__get_man_path()){
			return false;	
		}
		if($subpath){
			$p.="/".$subpath;	
		}
		
		return $this->mainap->get_sub_path_man($p,$mode);
	
	}
}
?>