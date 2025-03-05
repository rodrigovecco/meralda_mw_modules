<?php
class mwmod_mw_modulesman_ui_dirinfo extends mwmod_mw_modulesman_ui_abs{
	var $dirman;
	var $moduleinfoman;
	var $infomandata=array();
	function __construct($cod,$maininterface){
		$this->initui($cod,$maininterface);
		$this->set_def_title("Directory info");
		
	}
	
	function select_infoman_from_input($input){
		$this->infomandata=array();
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$mancod=$input->get_value_by_dot_cod("man")){
			return false;
		}
		if($mancod!=$input->get_value_by_dot_cod("selected.man")){
			return false;
		}
		if(!$path=$input->get_value_by_dot_cod("path")){
			return false;
		}
		if($path!=$input->get_value_by_dot_cod("selected.path")){
			return false;
		}
		
		if(!$dirman=$this->modulesman->get_dirman($mancod)){
			return false;
		}
		$this->dirman=$dirman;
		$moduleinfoman=false;
		$moduleinfoman=$this->dirman->create_dir_info_man($path);	
		if($moduleinfoman){
			if($moduleinfoman->check_path()){
				$this->infomandata["moduleinfoman"]=$moduleinfoman->get_debug_data();
				$this->moduleinfoman=$moduleinfoman;
				if($input->get_value_by_dot_cod("save")){
					$this->moduleinfoman->save_data($input->get_value_by_dot_cod("data"));
				}
				return true;
			}
		}
		return ;
			
	}
	
	function do_exec_page_in(){
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		$selectedok=false;
		if($this->select_infoman_from_input($input)){
			$selectedok=true;
		}
		//mw_array2list_echo($this->infomandata);
		//mw_array2list_echo($input->get_value());
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		$i=$cr->add_item(new mwmod_mw_datafield_hidden("man","man"));
		$i->set_value($input->get_value_by_dot_cod("selected.man"));
		$i=$cr->add_item(new mwmod_mw_datafield_hidden("path","path"));
		$i->set_value($input->get_value_by_dot_cod("selected.path"));
		
		$gr=$cr->add_item(new mwmod_mw_datafield_group("selected"));
		$i=$gr->add_item(new mwmod_mw_datafield_select("man","Manager"));
		$options=array();
		$mans=$this->modulesman->get_dirmans();
		$i->create_optionslist($mans);
		
		$i=$gr->add_item(new mwmod_mw_datafield_input("path","Path"));
		$gr->set_value($input->get_value_by_dot_cod("selected"));
		if($this->moduleinfoman){
			if($grdata=$this->moduleinfoman->get_data_inputs()){
				$cr->add_item($grdata);
				$i=$cr->add_item(new mwmod_mw_datafield_checkbox("save","Update data"));
			}
			$i=$cr->add_item(new mwmod_mw_datafield_html("info_file","Info file"));
			$i->set_value($this->moduleinfoman->get_info_file_full_path());
			
		}
		
		$submit=$cr->add_submit("Exec");
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();

		
	}

	function do_exec_no_sub_interface(){
	}
	
}
?>