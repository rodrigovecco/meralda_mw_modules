<?php

class mwmod_mw_helper_img_gr_imgsgr extends mwmod_mw_helper_img_abs{
	//private $img_path;
	//private $url;
	var $relative_heigth=1;
	var $min_new_img_heigth=20;
	var $min_new_img_width=20;
	private $_items=array();
	private $_sub_path_man;
	var $uploaded_filename;
	var $default_item_cod="def";
	var $_temp_subpath_man;
	public $mainSrcImgItem;
	var $uploaded_cfg=array();
	//var $uploaded_cfg=array("width"=>array("dim"=>1500,"mode"=>4,),"height"=>array("dim"=>1500,"mode"=>4,));
	function __construct(){
		
	}
	function delete(){
		if($imgman=$this->uploaded_img_subman()){
			$imgman->delete_all_files();	
		}
		if($subpath_man=$this->get_sub_path_man()){
			
			$subpath_man->delete();
			return true;
		}
		
		
		
			
	}
	function update_images_from_uploaded(){
		if(!$imgman=$this->uploaded_img_subman()){
			return false;	
		}
		if(!$src=$imgman->get_full_file_path()){
			return false;	
		}
		if(!$subpath_man=$this->get_sub_path_man()){
			return false;
		}
		$subpath_man->delete();
		
		$r=false;
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				if($subman=$item->new_img_subman()){
					if($n=$subman->copy_from_file($src)){
						$r=$n;
					}
				}
			}
		}
		$imgman->delete_all_files();
		return $r;
	
	}
	
	function check_upload_new_img_invalid_attemp($input,&$info=array(),&$invaliduploadattempt=false ){
		if(!$input){
			return false;
		}
		if(!is_array($input)){
			return false;	
		}
		if(!$imgman=$this->uploaded_img_subman()){
			return false;	
		}
		$result=$imgman->check_process_upload_input_invalid_attemp_quick($input,$info,$invaliduploadattempt);
		return $result;

	}
	function upload_new_img_and_proc($input){
		if(!$new=$this->upload_new_img($input)){
			
			return false;	
		}

		$this->uploaded_filename=$new;
		return $this->update_images_from_uploaded();
	}
	function upload_new_img_and_proc_by_inputname($inputname){
		$input=array("fileinputname"=>$inputname);
		return $this->upload_new_img_and_proc($input);
		
	}
	function upload_new_img($input){
		if(!$input){
			return false;
		}
		if(!is_array($input)){
			return false;	
		}
		if(!$imgman=$this->uploaded_img_subman()){

			return false;	
		}
		$name="img".date("YmdHis");

		if($new=$imgman->process_upload_input($input,$name)){
			return $new;
		}

	}
	function uploaded_img_subman(){
		if(!$pathman=$this->get_temp_sub_path_man()){
			return false;	
		}
		if(!$main=$this->mainimgman){
			return false;
		}
		if(!$path=$pathman->get_sub_path()){
			return false;	
		}
		$cfg=$this->uploaded_cfg;
		if(!$subman=$main->new_manager_from_cfg($cfg)){
			return false;	
		}
		$subman->set_img_path($path);
		if($this->uploaded_filename){
			$subman->current_filename=	$this->uploaded_filename;
		}
		return $subman;
	
		//if($subman=$main->

	}
	
	final function set_temp_sub_path_man($man){
		$this->_temp_subpath_man=$man;
	}
	final function get_temp_sub_path_man(){
		return $this->_temp_subpath_man;
	}
	
	function set_info_and_url_by_public_fakeurl($publicpath,$filename=false,$title=false,$realpath=false){
		if($realpath){
			if(is_object($realpath)){
				$this->set_sub_path_man($realpath);	
				$realpath=$realpath->get_path();	
			}
		}
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				if($realpath){
					$item->set_img_path($realpath."/$cod");	
				}
				if($filename){
					$url=$publicpath."/".$cod."/$filename";
					if($realpath){
						if(is_file($realpath."/$cod/".$filename)){
							if(file_exists($realpath."/$cod/".$filename)){
								$url=$publicpath."/".$cod."/$filename";
								$item->set_current_filename($filename);
							}
						}
					}else{
						
						$item->set_current_filename($filename);
					}
				}else{
					$url="";
					$item->set_current_filename();
				}
				$item->set_url($url);
				if($title){
					$item->set_title($title);	
				}
			}
		}
			
	}
	function set_info_and_url_by_public_path($publicpath,$filename=false,$title=false,$realpath=false){
		if($realpath){
			if(is_object($realpath)){
				$this->set_sub_path_man($realpath);	
				$realpath=$realpath->get_path();	
			}
		}
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				if($realpath){
					$item->set_img_path($realpath."/$cod");	
				}
				$url="";
				if($filename){
					if($realpath){
						if(is_file($realpath."/$cod/".$filename)){
							if(file_exists($realpath."/$cod/".$filename)){
								$url=$publicpath."/".$cod."/$filename";
								$item->set_current_filename($filename);
							}
						}
					}else{
						$url=$publicpath."/".$cod."/$filename";
						$item->set_current_filename($filename);
					}
				}else{
					$url="";
					$item->set_current_filename();
				}
				$item->set_url($url);
				if($title){
					$item->set_title($title);	
				}
			}
		}
	}
	function get_img_elem($cod=false){
		if(!$item=$this->get_item_or_def($cod)){
			return false;	
		}
		return $item->get_img_elem();
			
	}
	function get_img_url($cod=false){
		if(!$item=$this->get_item_or_def($cod)){
			return false;	
		}
		return $item->get_url();
	}
	final function set_sub_path_man($man){
		$this->_sub_path_man=$man;
	}
	final function get_sub_path_man(){
		return $this->_sub_path_man;
	}
	
	function is_enabled(){
		return $this->get_items_num();	
	}
	final function get_items_num(){
		return sizeof($this->get_items());
	}
	
	function get_debug_data(){
		$r=array();
		$r["relative_heigth"]=$this->relative_heigth;
		$r["min_new_img_heigth"]=$this->min_new_img_heigth;
		$r["min_new_img_width"]=$this->min_new_img_width;
		if($man=$this->get_sub_path_man()){
			$r["subpathman"]=$man->get_debug_data();	
		}
		if($man=$this->get_temp_sub_path_man()){
			$r["tempsubpathman"]=$man->get_debug_data();	
		}
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();	
			}
		}
		return $r;
	}
	function add_img_fixed_dim($cod,$width){
		if(!$item=$this->new_img($cod)){
			return false;	
		}
		if(!$width=$width+0){
			$width=$this->min_new_img_width;
		}
		$height=$this->get_heigth_by_width($width);
		$cfg=array(
			"width"=>array(
				"dim"=>$width,
				"mode"=>1,
			),
			"height"=>array(
				"dim"=>$height,
				"mode"=>1,
			)
			
		);
		$item->set_cfg($cfg);
		return $this->add_item($item);
	}

	function new_img($cod){
		$item= new mwmod_mw_helper_img_img($cod);
		return $item;
	}
	function set_min_img_dim($width,$heigth=false){
		if(!$width=$width+0){
			return false;
		}
		if(!$heigth=$heigth+0){
			$heigth=$this->get_heigth_by_width($width);
		}
		$this->min_new_img_width=$width;
		$this->min_new_img_heigth=$heigth;
		return true;
		
		
	}
	function get_heigth_by_width($width){
		return $width*$this->relative_heigth;	
	}
	function get_aspect_ratio(){
		return 1/$this->relative_heigth;
		//return $this->get_heigth_by_width
	}
	function set_relative_heigth($val){
		if($val=$val+0){
			$this->relative_heigth=$val;
			return true;
		}
	}
	final function get_item_or_def($cod=false){
		if($item=$this->get_item_active($cod)){
			return $item;	
		}
		if($item=$this->get_item_active($this->default_item_cod)){
			return $item;	
		}
		
	}
	function get_item_active($cod){
		return $this->get_item($cod,true);	
	}
	final function get_item($cod,$checkactive=false){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		if($this->_items[$cod]){
			if($checkactive){
				if(!$this->_items[$cod]->is_active()){
					return false;	
				}
			}
			return $this->_items[$cod];
		}
		return false;
	}
	
	final function add_item($item){
		$cod=$item->cod;
		$this->_items[$cod]=$item;
		return $item;
	}
	function get_items(){
		$r=array();
		if($items=$this->get_all_items()){
			foreach($items as $cod=>$item){
				if($item->is_active()){
					$r[$cod]=$item;	
				}
			}
		}
		return $r;
	}
	function afterItemsCreated(){
		if($items=$this->get_all_items()){
			foreach($items as $cod=>$item){
				$item->setGroup($this);
				if(!$this->mainSrcImgItem){
					if($item->isMainSrc){
						$this->mainSrcImgItem=$item;	
					}
				}
			}
			//mainSrcImgItem
		}
	}
	
	final function get_all_items(){
		return $this->_items;
	}
	
}
?>