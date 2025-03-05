<?php
class mwmod_mw_ui_main_uimaintemplate extends mwmod_mw_templates_basetemplate{
	private $main_ui;
	function __construct($ui){
		$this->set_mainap();
		$this->set_main_ui($ui);
		$this->htmlclasspref="sys_inteface_main";
	}
	function new_sub_interface_template($si){
		$t=new mwmod_mw_ui_sub_uitemplate($si);
		$t->htmlclasspref="sys_inteface_sub";
		return $t;
	}
	
	function exec_page_full_body_sub_interface_single_mode($subinterface){
		$subinterface->do_exec_page_single_mode();
	}

	
	function exec_page_full_body_sub_interface($subinterface){
		
		echo $this->get_html_tag_open("full");
		$this->exec_page_body_admin_top();
		$this->exec_page_body_admin_mnu();
		echo $this->get_html_tag_open("uibody");
		echo $this->get_html_tag_open("leftcol");
		$this->exec_page_body_admin_lat_mnu();
		echo "</div>";
		echo $this->get_html_tag_open("rightcol");
		//$this->exec_page_body_sub_interface($subinterface);
		$this->exec_page_body_sub_interface_final($subinterface);
		
		echo "</div>";
		echo "</div>";
		$this->exec_page_body_admin_bot();
		echo "</div>";
	
	}
	function exec_page_body_sub_interface_final($subinterface){
		if(!$subinterface){
			return false;	
		}
		if($last=$subinterface->get_this_or_final_current_subinterface()){
			return $this->exec_page_body_sub_interface($last);	
		}
	}

	
	
	function exec_page_body_sub_interface($subinterface){
		if(!$subinterface){
			return false;	
		}
		if(!$template=$subinterface->get_template($this)){
			return false;	
		}
		$template->exec_page_full_body_sub_interface();
	}
	
	function exec_page_body_admin_bot(){
		//
	}
	
	function exec_page_body_admin_lat_mnu(){
		//echo "sdfsd";
		if(!$mnu=$this->main_ui->get_lat_mnu()){
			return false;	
		}
		if(!$mnu->get_allowed_items_num()){
			return false;	
		}
		echo $this->get_html_tag_open("latmnu").$mnu->get_html()."</div>";
	
	}

	function exec_page_body_admin_mnu(){
		//echo "sdfsd";
		if(!$mnu=$this->main_ui->get_mnu()){
			return false;	
		}
		if(!$mnu->get_allowed_items_num()){
			return false;	
		}
		echo $this->get_html_tag_open("mnu").$mnu->get_html()."</div>";
	
	}

	function exec_page_body_admin_top(){
		echo $this->get_html_tag_open("top");
		echo $this->main_ui->get_page_title();
		echo "</div>";
	}
	function add_default_css_sheets($cssmanager){
		
		$cssmanager->add_item_by_cod_def_path("style.css");

	}


	final function set_main_ui($ui){
		$this->main_ui=$ui;	
	}
	final function __get_priv_main_ui(){
		return $this->main_ui;	
	}
	
	function __call($a,$b){
		return false;	
	}
	
}
?>