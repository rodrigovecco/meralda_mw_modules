<?php
//
class mwmod_mw_users_org_userdata extends mwmod_mw_users_userdata{
	function __construct($man){
		$this->init($man);
		
	}
	function create_profile_imgs_group($user){
		$man= new mwmod_mw_helper_img_gr_imgsgr();
		$this->init_profile_imgs_group($man,$user);
		return $man;
	}
	function set_cfg_profile_imgs_group($group,$user){
		$group->set_relative_heigth(1);
		$group->set_min_img_dim(504);
		$item=$group->add_img_fixed_dim("inline",16);
		$item->set_def_url("/images/user/user16x16.png");
		$item=$group->add_img_fixed_dim("tiny",32);
		$item->set_def_url("/images/user/user32x32.png");
		$item=$group->add_img_fixed_dim("small",40);
		$item->set_def_url("/images/user/user40x40.png");
		$item=$group->add_img_fixed_dim("profiletiny",50);
		$item->set_def_url("/images/user/user50x50.png");
		$item=$group->add_img_fixed_dim("profilesmall",140);
		$item->set_def_url("/images/user/user140x140.png");
		$item=$group->add_img_fixed_dim("profile",160);
		$item->set_def_url("/images/user/user160x160.png");
		$item=$group->add_img_fixed_dim("big",504);
		$item->set_def_url("/images/user/user504x504.png");
		
		$group->default_item_cod="profiletiny";
		
		
		
		
		
	}


	
	function add_inputs_user_data($grdata){
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->lng_get_msg_txt("complete_name","Nombre completo")));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("personal_email",$this->lng_get_msg_txt("personal_email","Correo personal")));
		$input->add_js_email_validation(true);
		

		
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("first_name",$this->lng_get_msg_txt("first_name","Nombre")));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("last_name",$this->lng_get_msg_txt("last_name","Apellido")));
		
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("out_of_office",$this->lng_get_msg_txt("out_of_office","Fuera de la oficina")));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_users_util_seluserinput("out_of_office_replacement",$this->lng_get_msg_txt("out_of_office_replacement","Remmplazo al estar fuera de la oficina")));
		
		
			
	}
	function set_allowed_change_user_data($nd,$allowed=false){
		if(!is_array($nd)){
			return false;
		}
		if(!$nd["complete_name"]){
			if($nd["first_name"]){
				if($nd["last_name"]){
					$nd["complete_name"]=	$nd["first_name"]." ".$nd["last_name"];
				}

			}
		}
		$notallowed=$this->get_not_allowed_data_change_keys($allowed);
		foreach($notallowed as $c){
			unset($nd[$c]);	
		}
		
		if(sizeof($nd)){
			return $nd;
		}

	}

}
?>