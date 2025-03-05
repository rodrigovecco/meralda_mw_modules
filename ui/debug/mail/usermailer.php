<?php
class mwmod_mw_ui_debug_mail_usermailer extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Usermailer");
		
	}
	function do_exec_no_sub_interface(){
	}
	function getTestUserMan(){
		return $this->mainap->get_user_manager();
	}
	function do_exec_page_in(){
		$userman=$this->getTestUserMan();
		$usermailer=$userman->get_user_mailer();
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$nd=array();
		$user=false;
		$msgitem=false;
		$send=false;
		if($_REQUEST["test"]??null){
			$nd=$_REQUEST["test"];
			if($_REQUEST["test"]["input"]??null){
				if($user=$userman->get_user($_REQUEST["test"]["input"]["userid"]??null)){
					echo "<div>Usuario: ".$user->get_real_and_idname()."</div>";
				}
				if($msgitem=$usermailer->getItem($_REQUEST["test"]["input"]["msg"]??null)){
					echo "<div>msgitem: ".$msgitem->get_name()."</div>";
				}
				$send=$_REQUEST["test"]["input"]["send"]??null;
			}
			
			/*
			$input=$_REQUEST["test"]["send"];
			if($input["send"]){
				$phpmailer=$mailerman->new_phpmailer();
				$phpmailer->msgHTML($input["html"]);
				$phpmailer->AltBody=$input["plain"];
				$phpmailer->addAddress($input["to"]);
				//echo $_REQUEST["test"]["to"];
				$phpmailer->Subject=$input["subject"];
				$phpmailer->SMTPDebug=4;
				if($phpmailer->send()){
					echo "<p>ok</p>";
				}else{
					echo "<p>error ".$phpmailer->ErrorInfo."</p>";
				}
				
				
			
			}
			*/
			////mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="test";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("input"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("userid","userid"));
		$i=$gr->add_item(new mwmod_mw_datafield_select("msg","msg"));
		$i->create_optionslist($usermailer->getItems());
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("send","send"));
		
		$submit=$cr->add_submit("Enviar");
		$cr->set_data($nd);	
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();
		if($msgitem){
			if($user){
				if($send){
					/*
					if($phpmailer=$msgitem->getReadyMailerForUser($user)){
						echo get_class($phpmailer);	
						$phpmailer->SMTPDebug=4;
						if($phpmailer->send()){
							echo "<p>ok</p>";
						}else{
							echo "<p>error ".$phpmailer->ErrorInfo."</p>";
						}

					}
					*/
					
					//echo get_class($msgitem);
					$ok=false;
					if($phpmailer=$msgitem->getReadyMailerForUser($user)){
						$phpmailer->SMTPDebug=4;
						if($phpmailer->send()){
							$ok=true;	
						}
					}
					if($ok){
						echo "<div>enviado</div>";	
					}else{
						echo "<div>no enviado</div>";	
					}
					/*
					if($msgitem->sendForUser($user)){
						echo "<div>enviado</div>";	
					}else{
						echo "<div>no enviado</div>";	
					}
					*/
					
				}
				if($phgr=$msgitem->getReadyPHPprocessorsGrForUser($user)){
					
					mw_array2list_echo($phgr->get_debug_data_for_mail());
				}
			}
		}
		
		mw_array2list_echo($usermailer->getDebugData());
		
		return false;
		$lngman=$this->mainap->get_submanager("lng");
		echo get_class($lngman)."<br>";
		$mailmsgsman=$lngman->get_mail_msgs_man();
		echo get_class($mailmsgsman)."<br>";
	
		$mailmsgsitem=$mailmsgsman->get_item("user_reset_pass_request");
		mw_array2list_echo($mailmsgsitem->get_files_locations_info());
		
		
		
		$mailerman=$this->mainap->get_submanager("sysmail");
		//mw_array2list_echo($mailerman->get_cfg_data());
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		if($_REQUEST["test"]["send"]){
			$input=$_REQUEST["test"]["send"];
			if($input["send"]){
				$phpmailer=$mailerman->new_phpmailer();
				$phpmailer->msgHTML($input["html"]);
				$phpmailer->AltBody=$input["plain"];
				$phpmailer->addAddress($input["to"]);
				//echo $_REQUEST["test"]["to"];
				$phpmailer->Subject=$input["subject"];
				$phpmailer->SMTPDebug=4;
				if($phpmailer->send()){
					echo "<p>ok</p>";
				}else{
					echo "<p>error ".$phpmailer->ErrorInfo."</p>";
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
		if($_REQUEST["test"]){
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
	
}
?>