<?php
class mwmod_mw_modulesman_ui_classinfo extends mwmod_mw_modulesman_ui_abs{
	var $dirman;
	var $moduleinfoman;
	var $infomandata=array();
	function __construct($cod,$maininterface){
		$this->initui($cod,$maininterface);
		$this->set_def_title("PHP class");
		
	}
	
	function select_class_from_input($input){
		$this->infomandata=array();
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$class=$input->get_value_by_dot_cod("class")){
			return false;
		}
		if($class!=$input->get_value_by_dot_cod("selected.class")){
			return false;
		}
		if($input->get_value_by_dot_cod("selected.single_file")!=$input->get_value_by_dot_cod("single_file")){
			return false;
		}
		if(!$dirman=$this->modulesman->get_autoload_dirman_for_class($class)){
			
			return false;
		}
		$this->dirman=$dirman;
		$moduleinfoman=false;
		if($input->get_value_by_dot_cod("selected.single_file")){
			$moduleinfoman=$this->dirman->create_single_file_info_man_for_class($class);	
		}else{
			$moduleinfoman=$this->dirman->create_dir_info_man_for_class($class);	
		}
		if($moduleinfoman){
			if($moduleinfoman->check_path()){
				$this->infomandata["moduleinfoman"]=$moduleinfoman->get_debug_data();
				$this->moduleinfoman=$moduleinfoman;
				if($input->get_value_by_dot_cod("save")){
					$this->moduleinfoman->save_data($input->get_value_by_dot_cod("data"));
				}
			}
			return true;
		}
		//$this->infomandata["classinfo"]=$dirman->autoloadclassman->get_class_info($class);
		
			
	}
	
	function do_exec_page_in(){
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		$selectedok=false;
		if($this->select_class_from_input($input)){
			//mw_array2list_echo($this->dirman->get_debug_data());
			$selectedok=true;
		}
		//mw_array2list_echo($this->infomandata);
		//mw_array2list_echo($input->get_value());
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		$i=$cr->add_item(new mwmod_mw_datafield_hidden("class","Class"));
		$i->set_value($input->get_value_by_dot_cod("selected.class"));
		$i=$cr->add_item(new mwmod_mw_datafield_hidden("single_file","single_file"));
		$i->set_value($input->get_value_by_dot_cod("selected.single_file"));
		
		$gr=$cr->add_item(new mwmod_mw_datafield_group("selected"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("class","Class"));
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("single_file","Single file"));
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