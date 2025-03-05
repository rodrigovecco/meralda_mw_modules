<?php
class mwmod_mw_ui_debug_mail extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("MAIL");
		$this->sucods="usermailer,more";//su1,su2
		
	}
	function _do_create_subinterface_child_more(){
		$si=new mwmod_mw_mail_ui_debug_main("more",$this);
		return $si;
	}
	function _do_create_subinterface_child_usermailer(){
		$si=new mwmod_mw_ui_debug_mail_usermailer("usermailer",$this);
		return $si;
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$lngman=$this->mainap->get_submanager("lng");
		echo get_class($lngman)."<br>";
		$mailmsgsman=$lngman->get_mail_msgs_man();
		echo get_class($mailmsgsman)."<br>";
	
		$mailmsgsitem=$mailmsgsman->get_item("user_reset_pass_request");
		//mw_array2list_echo($mailmsgsitem->get_files_locations_info());
		
		
		
		$mailerman=$this->mainap->get_submanager("sysmail");
		//mw_array2list_echo($mailerman->get_cfg_data());
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		if(is_array($_REQUEST["test"]??null) and $_REQUEST["test"]["send"]??null){
			$input=$_REQUEST["test"]["send"];
			if($input["send"]??null){
				$phpmailer=$mailerman->new_phpmailer();
				
				$phpmailer->msgHTML("<!doctype html>".$input["html"]);
				if($input["plain"]??null){
					$phpmailer->AltBody=$input["plain"];
				}
				$to=explode(";",$input["to"]??null."");
				//mw_array2list_echo($to);
				
				foreach($to as $t){
					if($t=trim($t)){
						$phpmailer->addAddress($t);
					}
				}
				

				//$phpmailer->addAddress($input["to"]);
				//echo $_REQUEST["test"]["to"];
				$phpmailer->Subject=$input["subject"]??null;
				$phpmailer->SMTPDebug=4;
				$phpmailer->Debugoutput="html";
				if($phpmailer->send()){
					echo "<p>ok</p>";
				}else{
					echo "<p>error ".nl2br($phpmailer->ErrorInfo)."</p>";
				}
				
				
			
			}
			////mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="test";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("send"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("to","to"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("subject","subject"));
		$i=$gr->add_item(new mwmod_mw_datafield_textarea("plain","plain"));
		$i=$gr->add_item(new mwmod_mw_datafield_textarea("html","html"));
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("send","send"));
		
		$submit=$cr->add_submit("Enviar");
		if($_REQUEST["test"]??null){
			$cr->set_data($_REQUEST["test"]);	
		}
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();
		
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}

	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}

	
}
?>