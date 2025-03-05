<?php
//
class mwmod_mw_users_org_user extends mwmod_mw_users_user{
	function __construct($man,$tblitem){
		$this->init($man,$tblitem);	
	}
	/*
	function get_img_url(){
		if(!$f=$this->tblitem->get_data("image")){
			return false;
		}
		return $this->mainap->get_public_userfiles_url_path()."/".$this->get_rel_path()."/imgs/def/$f";
		
	}
	function save_imgs_from_input($input){
		if(!is_array($input)){
			return false;	
		}
		$this->save_img_def_from_input($input["img"]);
		//mw_array2list_echo($input);

	}
	function save_img_def_from_input($input){
		if(!is_array($input)){
			return false;	
		}
		$imgman=$this->new_img_man();
		//process_upload_input_delete
		if($new=$imgman->process_upload_input_delete($input)){
			$nd=array(
				"image"=>""
			);
			$this->tblitem->do_update($nd);
		}
	
		if($new=$imgman->process_upload_input($input)){
			$nd=array(
				"image"=>$new
			);
			$this->tblitem->do_update($nd);
		}
		return;

	}
	
	function new_img_man(){
		$main=new mwmod_mw_helper_img_imgman();
		$cfg=array(
			"width"=>array("dim"=>70,"mode"=>"max"),
			"height"=>array("dim"=>70,"mode"=>"max"),
		);
		$man=$main->new_manager_from_cfg($cfg);	
		$pathman=$this->get_imgs_path_man();
		$path=$pathman->get_sub_path("def");
		
		$man->set_img_path($path);
		if($f=$this->tblitem->get_data("image")){
			$man->set_current_filename($f);
		}
		//
		return $man;
	}
	function get_imgs_path_man(){
		
		
		$rel=$this->get_rel_path();
		return $this->mainap->get_sub_path_man($rel."/imgs","userfilespublic");
		
	}
	*/

}
?>