<?php

class mwmod_mw_helper_croppermaster_uploaderhtml extends mwmod_mw_bootstrap_html_template_abs{
	var $upload_url;
	var $input_src_name="avatar_src";
	var $input_data_name="avatar_data";
	var $input_file_name="avatar_file";
	var $input_file_id="avatarInput";
	
	public $frm;
	function __construct($upload_url=false){
		$this->init_croppermaster($upload_url);
		$main=new mwmod_mw_bootstrap_html_def();
		$this->create_cont($main);

	}
	function get_upload_input(){
		$r=array();
		$r["fileinputname"]=$this->input_file_name;
		//$r["cropoptions"]=$this->crop_data_to_array($_REQUEST[$this->input_data_name]);
		$r["cropoptions"]=$_REQUEST[$this->input_data_name]??null;
		return $r;
		
	}
	/*
	function crop_data_to_array($input){
		if(!$input=trim($input)){
			return false;	
		}
		$pairs=explode("|",$input);
		$r=array();
		foreach($pairs as $pair){
			if($pair=trim($pair)){
				$pair_a=explode(":",$pair);
				if($cod=trim($pair_a[0])){
					$r[$cod]=$pair_a[1]+0;	
				}
			}
		}
		return $r;
	}
	*/
	function create_cont($main){
		$this->set_main_elem($main);
		$form=new mwmod_mw_bootstrap_html_def("avatar-form","form");
		$form->set_att("enctype","multipart/form-data");
		$this->frm=$form;
		$form->set_att("method","post");
		$this->set_key_cont("form",$form);
		$main->add_cont($form);
		$upload_elem=new mwmod_mw_bootstrap_html_def("avatar-upload");
		$this->set_key_cont("uploadcontainer",$upload_elem);
		$form->add_cont($upload_elem);
		
		$c=$upload_elem;
		
		//<div class="avatar-upload">
		$input=new mwmod_mw_bootstrap_html_def("avatar-src","input");//
		$input->set_att("type","hidden");
		$this->set_key_cont("input_src",$input);
		$c->add_cont($input);
		
		$input=new mwmod_mw_bootstrap_html_def("avatar-data","input");//avatar-data
		$input->set_att("type","hidden");
		$this->set_key_cont("input_data",$input);
		$c->add_cont($input);
		
		$lbl=new mwmod_mw_bootstrap_html_def(false,"label");
		$lbl->add_cont($this->lng_get_msg_txt("select_file","Seleccionar archivo"));
		$this->set_key_cont("select_file_lbl",$lbl);
		$c->add_cont($lbl);
		
		$input=new mwmod_mw_bootstrap_html_def("avatar-input","input");//avatar-input
		$input->set_att("type","file");
		$this->set_key_cont("input_file",$input);
		$c->add_cont($input);
		
		
		// <div class="avatar-wrapper"></div>
		
		$elem=new mwmod_mw_bootstrap_html_def("avatar-wrapper");
		$this->set_key_cont("wrapper",$elem);
		$form->add_cont($elem);
		
		////other btns
		$btnsgr=$form->add_cont_elem();
		$btnsgr->setAtts('class="btn-group" role="group"');
		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button" data-option="0.1" data-method="zoom"');
		$btn->add_class("btn btn-primary avatar-btns");
		$btn->add_cont_elem("","span");
		$btn->add_class("fa fa-search-plus");

		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button" data-option="-0.1" data-method="zoom"');
		$btn->add_class("btn btn-primary avatar-btns");
		$btn->add_cont_elem("","span");
		$btn->add_class("fa fa-search-minus");
		
		
		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-method="reset"');
		$btn->add_class("btn btn-primary avatar-btns");
		$btn->add_cont_elem("","span");
		$btn->add_class("fa fa-redo");
		
		
		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-cmd="fullW"');
		$btn->add_class("btn btn-primary avatar-btns");
		$btn->add_cont_elem("","span");
		$btn->add_class("fa fa-arrows-alt-h");
		
		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-cmd="fullH"');
		$btn->add_class("btn btn-primary avatar-btns");
		$btn->add_cont_elem("","span");
		$btn->add_class("fa fa-arrows-alt-v");
		
		
		
		
		
		
		
		
		$elem=new mwmod_mw_bootstrap_html_specialelem_btn($this->lng_get_msg_txt("done","Listo"));
		$elem->add_additional_class("btn-block");
		$elem->add_additional_class("avatar-save");
		$elem->add_additional_class("mx-2");

		
		$this->set_key_cont("submit_btn",$elem);
		$form->add_cont($elem);
		//<button class="btn btn-primary btn-block avatar-save" type="submit">Done</button>
		
		$elem=new mwmod_mw_bootstrap_html_def("loading");
		$elem->set_att("aria-label","Loading");
		$elem->set_att("role","img");
		$elem->set_att("tabindex","-1");
		$this->set_key_cont("loading",$elem);
		$form->add_cont($elem);
		
				
		
		$this->update_other_elems();
		//extender
	}
	function set_upload_url($url){
		$this->upload_url=$url;
		$this->update_other_elems();
	}

	function update_other_elems(){
		if($elem=$this->get_key_cont("form")){
			if($this->upload_url){
				$elem->set_att("action",$this->upload_url);
			}
		}
		if($elem=$this->get_key_cont("input_src")){
			$elem->set_att("name",$this->input_src_name);
		}
		if($elem=$this->get_key_cont("input_data")){
			$elem->set_att("name",$this->input_data_name);
		}
		if($elem=$this->get_key_cont("select_file_lbl")){
			$elem->set_att("for",$this->input_file_id);
		}
		if($elem=$this->get_key_cont("input_file")){
			$elem->set_att("name",$this->input_file_name);
			$elem->set_att("id",$this->input_file_id);
		}
		
		/*
		if($elem=$this->main_elem){
			if($this->id){
				$elem->set_id($this->id);
			}
			$elem->set_fade($this->fade);
			$elem->set_small($this->small);
		}
		if($elem=$this->openbtn){
			if($this->id){
				$elem->set_att("data-target","#".$this->id);
			}
		}
		*/
		
	}

	function init_croppermaster($upload_url=false){
		$this->upload_url=$upload_url;
		$this->set_lngmsgsmancod("croppermaster");
	}
}
?>