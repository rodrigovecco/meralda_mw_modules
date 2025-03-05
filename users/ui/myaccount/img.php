<?php
class mwmod_mw_users_ui_myaccount_img extends mwmod_mw_users_ui_myaccount_abs{
	var $msgs;
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("profile_img","Imagen de perfil"));
		
	}
	function do_exec_no_sub_interface(){
		$cmutil=new mwmod_mw_helper_croppermaster_util();
		$cmutil->preapare_ui($this);
		$cmutil->preapare_ui_avatar($this);
		$this->msgs=new mwmod_mw_html_elem();
		if($user=$this->get_admin_current_user()){
			$dm=$user->get_user_data_man();
			if($_REQUEST["deleteimg"]??false){
				$dm->delete_profile_img($user);	
			}
			$mwmod_mw_helper_croppermaster_uploaderhtml =new mwmod_mw_helper_croppermaster_uploaderhtml();
			if($input=$mwmod_mw_helper_croppermaster_uploaderhtml->get_upload_input()){
				if($dm->upload_profile_imgs_from_input_crop($input,$user,$this->msgs)){
					$done=true;
				}
					
			}

		}
		
		
	}

	function do_exec_page_in(){
		if(!$user=$this->get_admin_current_user()){
			return false;
		}
		
		
		
		$msgs=$this->msgs;
		$msgs->only_visible_when_has_cont=true;
		$dm=$user->get_user_data_man();
		$mwmod_mw_helper_croppermaster_uploaderhtml =new mwmod_mw_helper_croppermaster_uploaderhtml();
		/*
		if($input=$mwmod_mw_helper_croppermaster_uploaderhtml->get_upload_input()){
			
			
			if($dm->upload_profile_imgs_from_input_crop($input,$user,$msgs)){
				$done=true;
			}

		}
		*/
		echo "<div id='crop-avatar'>";
		$params=new mwmod_mw_jsobj_obj();
		if($imgsgr=$user->profile_imgs_group){
			$params->set_prop("aspectRatio",$imgsgr->get_aspect_ratio());
		}
		
		$modal= new mwmod_mw_bootstrap_html_template_modal("avatar-modal",$this->lng_get_msg_txt("profile_img","Imagen de perfil"));
		//$modal= new mwmod_mw_bootstrap_html_template_modal("xcvxcv-modal",$this->lng_get_msg_txt("profile_img","Imagen de perfil"));
		//$modal->cont_elem->add_cont("sdfsdfsdfs");
		$modal_cont=$modal->cont_elem;
		
		
		$modal_cont->add_cont($mwmod_mw_helper_croppermaster_uploaderhtml);
		
		
		$panel= new mwmod_mw_bootstrap_html_template_panel();
		if($title=$panel->get_key_cont("title")){
			$title->add_cont($this->lng_get_msg_txt("profile_img","Imagen de perfil"));	
		}
		if($imgelem=$user->get_img_elem("big")){
			$imgscontainer=new mwmod_mw_html_elem();
			$imgscontainer->set_style("text-align","center");
			$imgscontainer->add_cont($imgelem);	
			$panel->cont_elem->add_cont($imgscontainer);
			
		}
		
		$btncontainer=new mwmod_mw_templates_html_btnscontainer();
		if($user->has_img()){
			$url=$this->get_url(array("deleteimg"=>"true"));
			$jsevent=new mwmod_mw_jsobj_codecontainer();
			$msg=$jsevent->get_txt($this->lng_get_msg_txt("confirm_delete_image","¿Realmente desea eliminar su imagen?"));
			$jsevent->add_cont("if(confirm('$msg')){window.location='$url'}else{return false}");
			
			$btn=new mwmod_mw_bootstrap_html_specialelem_btn($this->lng_get_msg_txt("delete_image","Eliminar imagen"),"warning");
			
			$btn->set_att("onclick",$jsevent->get_as_js_val());
			$btncontainer->add_cont($btn);		
		}
		$btn=$modal->new_open_btn($this->lng_get_msg_txt("change_image","Cambiar imagen"));
		$btncontainer->add_cont($btn);		
		if($footer=$panel->get_key_cont("footer")){
			$footer->add_cont($btncontainer);	
		}else{
			$panel->cont_elem->add_cont($btncontainer);
		}
		$panel->do_output();
		$modal->do_output();
		echo "</div>";
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$jsin="var crophelper=new CropAvatar($('#crop-avatar'),".$params->get_as_js_val()."); crophelper.initPreview(); ";
		$js->add_cont($jsin);
		echo $js->get_js_script_html();
		
		if($msgs){
			echo $msgs->get_as_html();
		}
		
		
		
		return true;
		
		

		
	}
	
}
?>