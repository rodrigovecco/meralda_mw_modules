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

		// Max upload size derived from php.ini (upload_max_filesize / post_max_size).
		$fm=new mwmod_mw_helper_fileman();
		$maxbytes=(int)$fm->get_max_upload_size_bytes();
		$maxlabel=$maxbytes>0?$fm->format_bytes($maxbytes):"";

		// Standard HTML hint for the browser: MAX_FILE_SIZE must appear before the file input.
		$maxinput=new mwmod_mw_bootstrap_html_def(false,"input");
		$maxinput->set_att("type","hidden");
		$maxinput->set_att("name","MAX_FILE_SIZE");
		$maxinput->set_att("value",(string)$maxbytes);
		$c->add_cont($maxinput);

		$lbl=new mwmod_mw_bootstrap_html_def(false,"label");
		$lbltxt=$this->lng_get_msg_txt("select_file","Seleccionar archivo");
		if($maxlabel){
			$lbltxt.=" (".$this->lng_get_msg_txt("max_short","máx.")." ".$maxlabel.")";
		}
		$lbl->add_cont($lbltxt);
		$this->set_key_cont("select_file_lbl",$lbl);
		$c->add_cont($lbl);

		$input=new mwmod_mw_bootstrap_html_def("avatar-input","input");//avatar-input
		$input->set_att("type","file");
		if($maxbytes>0){
			// Read client-side by avatar.js to reject oversized files before upload.
			$input->set_att("data-max-file-size",(string)$maxbytes);
			$input->set_att("data-max-file-size-label",$maxlabel);
		}
		$this->set_key_cont("input_file",$input);
		$c->add_cont($input);


		// <div class="avatar-wrapper"></div>

		$elem=new mwmod_mw_bootstrap_html_def("avatar-wrapper");
		$this->set_key_cont("wrapper",$elem);
		// 🧩 Estilos de seguridad para evitar el problema visual:
		$elem->set_att("style", implode(";", [
			"width:100%",
			"max-width:100%",
			"min-height:400px",     // altura visible antes de cargar imagen
			"max-height:75vh",      // evita que se salga de la pantalla
			"margin:0 auto",
			"overflow:hidden",
			"background-color:#f3f3f3",
			"display:flex",
			"justify-content:center",
			"align-items:center"
		]));


		$form->add_cont($elem);

		////other btns
		$btnsgr=$form->add_cont_elem();
		$btnsgr->setAtts('class="btn-group" role="group"');
		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button" data-option="0.1" data-method="zoom"');
		$btn->add_class("btn btn-primary avatar-btns");
		$icon=$btn->add_cont_elem("","span");
		$icon->add_class("fa fa-search-plus");

		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button" data-option="-0.1" data-method="zoom"');
		$btn->add_class("btn btn-primary avatar-btns");
		$icon=$btn->add_cont_elem("","span");
		$icon->add_class("fa fa-search-minus");


		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-method="reset"');
		$btn->add_class("btn btn-primary avatar-btns");
		$icon=$btn->add_cont_elem("","span");
		$icon->add_class("fa fa-redo");


		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-cmd="fullW"');
		$btn->add_class("btn btn-primary avatar-btns");
		$icon=$btn->add_cont_elem("","span");
		$icon->add_class("fa fa-arrows-alt-h");

		$btn=$btnsgr->add_cont_elem("","button");
		$btn->setAtts('type="button"  data-cmd="fullH"');
		$btn->add_class("btn btn-primary avatar-btns");
		$icon=$btn->add_cont_elem("","span");
		$icon->add_class("fa fa-arrows-alt-v");








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
