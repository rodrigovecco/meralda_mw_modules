<?php
abstract class  mwmod_mw_manager_itemabs extends mw_apsubbaseobj{
	private $id;
	private $tblitem;
	private $man;
	private $_treedataman;
	private $_strdataman;
	private $_xmldataman;
	private $_can_create_strdata=false;
	private $_can_create_treedata=false;
	private $_can_create_xmltreedata=false;
	private $_can_create_jsondata=false;
	private $jsonDataMan;
	
	
	private $_related_objects_man;
	//20250202 HAC 
	function getDataAsDate($format="c"){
		$r=$this->get_data();

		if($this->tblitem){
			if($fields=$this->tblitem->tblman->getFields()){
				foreach($fields as $cod=>$field){
					if($field->isDate()){
						if(array_key_exists($cod,$r)){
							$t=$this->tblitem->get_data_as_time($cod);
							if($t){
								$r[$cod]=date($format,$t);	
							}else{
								$r[$cod]=null;
							}
						}
					}
				}
			}
		}
		return $r;
	}
	function create_related_objects_man(){
		return false;	
	}
	function getDataForDXtbl(){
		$r=$this->get_data();
		if($zeroAsNull=$this->man->getZeoroAsNullFieldsCods()){
			foreach($zeroAsNull as $cod){
				if(isset($r[$cod])){
					if(!$r[$cod]){
						$r[$cod]=NULL;	
					}
				}
			}
		}
		if($this->tblitem){
			if($fields=$this->tblitem->tblman->getFields()){
				foreach($fields as $cod=>$field){
					if($field->isDate()){
						if(array_key_exists($cod,$r)){
							$r[$cod]=$this->tblitem->get_date_js($cod);	
						}
					}
				}
			}
		}
		return $r;
	}
	final function get_related_objects_man(){
		if(!isset($this->_related_objects_man)){
			if(!$this->_related_objects_man=$this->create_related_objects_man()){
				$this->_related_objects_man=false;	
			}
		}
		return $this->_related_objects_man;
	}
	
	
	
	//json
	final function enable_jsondata($val=true){
		$this->_can_create_jsondata=$val;
	}
	function getJsonDataItem($code="data",$path=false){
		if($m=$this->__get_priv_jsonDataMan()){
			return $m->get_datamanager($code,$path);	
		}
	}
	
	function createJsonDataMan(){
		if(!$this->jsonDataManEnabled()){
			return false;
		}
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		$path=$p."/data";
		$man=new mwmod_mw_data_json_man($path);
		return $man;
	}
	final function jsonDataManEnabled(){
		return $this->_can_create_jsondata;
	}

	final function __get_priv_jsonDataMan(){
		if(!isset($this->jsonDataMan)){
			if(!$this->jsonDataMan=$this->createJsonDataMan()){
				$this->jsonDataMan=false;
			}
		}
		return $this->jsonDataMan;
	}
	
	
	/////xml data
	function get_xmldata_item($code="data",$path=false){
		if($m=$this->get_xmldataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_xmldataman(){
		if(isset($this->_xmldataman)){
			return 	$this->_xmldataman;
		}
		if($m=$this->get_init_xmldataman()){
			$this->_xmldataman=$m;
			return 	$this->_xmldataman;
		}
	}
	final function can_create_xmldata(){
		
		return $this->_can_create_xmltreedata;	
	}
	function get_init_xmldataman(){
		if(!$this->can_create_xmldata()){
			return false;		
		}
		if(!$p=$this->get_xmldata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_xmltree_man($p);
		return $m;
	}
	function get_xmldata_path(){
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		
		return $p."/xml";	
	}

	
	
	/////////
	function mwmod_mw_manager_item($tblitem,$man){
		$this->init($tblitem,$man);	
	}
	function allow_admin(){
		return $this->man->allow_admin();
	}
	function allow_delete(){
		return false;
	}
	function get_filemanager(){
		return $this->man->get_filemanager();
	}
	final function enable_xmldata($val=true){
		$this->_can_create_xmltreedata=$val;
	}
	
	final function enable_strdata($val=true){
		$this->_can_create_strdata=$val;
	}
	final function enable_treedata($val=true){
		$this->_can_create_treedata=$val;
	}
	/*
	function get_content_html(){
		//debe extenderse
		return $this->get_name();	
	}
	*/
	
	function delete(){
		if(!$this->allow_delete()){
			return false;	
		}
		return $this->do_delete();
		
	}
	final function _delete_all_files(){
		if(!$fm=$this->mainap->get_submanager("fileman")){
			return false;	
		}
		if($p=$this->get_path(false,true)){
			if(!$fm->delete_path($p)){
				return false;	
			}
		}
		if($p=$this->get_path(false,false)){
			if(!$fm->delete_path($p)){
				return false;	
			}
		}
		return true;
	}
	final function _delete_tbl_item(){
		if($tblitem=$this->tblitem){
			return $tblitem->delete();
		}
	
	}
	function do_delete(){
		return $this->_delete();	
	}
	final function _delete(){
		if(!$this->pre_delete_depending_objects()){
			return false;	
		}
		$this->_delete_all_files();
		if(!$this->_delete_tbl_item()){
			return false;	
		}
		$this->post_delete();
		return true;
		
		
	}
	function post_delete(){
		//extender
		return true;
	}
	function pre_delete_depending_objects(){
		//extender
		//false si no sepuede eliminar
		return true;
	}
	
	
	
	function get_strdata_item($code="data",$path=false){
		if($m=$this->get_strdataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_strdataman(){
		if(isset($this->_strdataman)){
			return 	$this->_strdataman;
		}
		if($m=$this->get_init_strdataman()){
			$this->_strdataman=$m;
			return 	$this->_strdataman;
		}
	}
	function can_create_strdata(){
		
		return $this->_can_create_strdata;	
	}
	function get_init_strdataman(){
		if(!$this->can_create_strdata()){
			return false;		
		}
		if(!$p=$this->get_strdata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_str_man($p);
		return $m;
	}
	function get_strdata_path(){
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		
		return $p."/str";	
	}
////
	
	
	function get_treedata_item($code="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_treedataman(){
		if(isset($this->_treedataman)){
			return 	$this->_treedataman;
		}
		if($m=$this->get_init_treedataman()){
			$this->_treedataman=$m;
			return 	$this->_treedataman;
		}
	}
	function can_create_treedata(){
		return $this->_can_create_treedata;	
	}
	function get_init_treedataman(){
		if(!$this->can_create_treedata()){
			return false;		
		}
		if(!$p=$this->get_treedata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_tree_man($p);
		return $m;
	}
	function get_treedata_path(){
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		
		return $p."/data";	
	}
	
	function get_sub_path_man($subpath=false,$public=false){
		//nueva
		if($public){
			$mode="userfilespublic";		
		}else{
			$mode="userfiles";	
		}
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		if($subpath){
			$p.="/".$subpath;	
		}
		
		return $this->mainap->get_sub_path_man($p,$mode);
	
	}

	
	function get_path($subpath=false,$public=true){
		if($public){
			$mode="userfilespublic";		
		}else{
			$mode="userfiles";	
		}
		if(!$p=$this->__get_items_path()){
			return false;	
		}
		if($subpath){
			$p.="/".$subpath;	
		}
		
		return $this->mainap->get_sub_path($p,$mode);
			
	}

	function __get_items_path(){
		if(!$p=$this->man->__get_items_path()){
			return false;	
		}
		if(!$id=$this->get_id()){
			return false;	
		}
		return $p."/".$id;
	}
	function get_name(){
		return $this->man->get_item_name($this);	
		
		//return $this->get_data("name");	
	}
	function after_created($data){
		//
	}
	function do_save($input){
		if(!is_array($input)){
			return false;	
		}
		$this->do_save_data($input["data"]);
	
	}
	
	function do_save_data($input){
		if($tblitem=$this->tblitem){
			return $tblitem->update($input);
		}
	
	}
	function validate_save_input_data(&$input){
		if(!is_array($input)){
			return false;	
		}
		return true;
		
			
	}
	
	function validate_save_input(&$input){
		if(!is_array($input)){
			return false;	
		}
		
		return $this->validate_save_input_data($input["data"]);
	}
	function save($input){
		if(!$this->allow_admin()){
			return false;	
		}
		if(!$this->validate_save_input($input)){
			return false;	
		}
		return $this->do_save($input);
	}
	function set_admin_inputs($gr){
		$subgr=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$this->set_admin_inputs_data($subgr);
	
	}
	function set_admin_inputs_data($gr){
		$this->man->set_new_item_inputs_data($gr,$this);
		//
	}
	function get_datafield_creator(){
		if(!$cr=$this->man->get_item_datafield_creator($this)){
			return false;	
		}
		$data=$this->get_admin_frm_data();
		$cr->set_data($data);
		return $cr;
	}
	function get_public_url_path(){
		if(!$p=$this->man->get_public_url_path()){
			return false;	
		}
		return $p."/".$this->get_id()."/";
	}
	function get_admin_frm_data(){
		$r=array();
		$r["data"]=$this->get_data();
		return $r;	
	}
	function is_available(){
		return true;
	}	
	final function get_id(){
		return $this->id;		
	}
	function get_isset_data($key){
		if($tblitem=$this->tblitem){
			return $tblitem->get_isset_data($key);
		}
	}
	function get_data($key=""){
		if($tblitem=$this->tblitem){
			return $tblitem->get_data($key);
		}
	}
	function get_data_as_date($key=""){
		if($tblitem=$this->tblitem){
			return $tblitem->get_data_as_date($key);
		}
	}
	
	final function __get_priv_id(){
		return $this->id; 	
	}
	final function __get_priv_tblitem(){
		return $this->tblitem; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	function __toString(){
		if($r=$this->get_name()){
			return $r;
		}
		if($tblitem=$this->tblitem){
			return $tblitem->__toString();
		}
		return $this->get_id();
	}
	function __call($a,$b){
		return false;	
	}
	
	final function init($tblitem,$man){
		$ap=$man->mainap;
		$this->tblitem=$tblitem;
		$this->man=$man;
		$this->id=$this->tblitem->get_id();
		$this->set_mainap($ap);	
		
	
	}
	
}


?>