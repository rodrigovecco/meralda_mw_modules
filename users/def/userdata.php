<?php
//
class mwmod_mw_users_def_userdata extends mwmod_mw_users_userdata{
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



}
?>