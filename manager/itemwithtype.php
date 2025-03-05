<?php
class  mwmod_mw_manager_itemwithtype extends mwmod_mw_manager_item{
	private $type;

	function __construct($tblitem,$type){
		$this->init_from_type($tblitem,$type);	
	}
	
	function get_datafield_creator(){
		$cr=new mwmod_mw_datafield_creator();
		$input_id=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("id",$this->get_msg("ID")));
		$input_type=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("type_name",$this->get_msg("Tipo")));
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->get_msg("Nombre")),"data");
		$input->set_required(true);
		
		
		
		$data=$this->get_admin_frm_data();
		$cr->set_data($data);
		$input_id->set_value($this->get_id());
		$input_type->set_value($this->type->get_name());
		
		return $cr;
	}

	function do_save_data($input){
		if($tblitem=$this->tblitem){
			unset($input["type"]);
			return $tblitem->update($input);
		}
	
	}
	 
	final function __get_priv_type(){
		return $this->type; 	
	}
	final function init_from_type($tblitem,$type){
		$man=$type->man;
		$this->init($tblitem,$man);
		$this->set_type($type);
	}
	final function set_type($type){
		$this->type=$type;
	}
	
}


?>