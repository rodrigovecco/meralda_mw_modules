<?php
class mwmod_mw_mail_ui_debug_sendmail extends mwmod_mw_ui_sub_uiabs{
	private $testDataMan;
	public $contentInput;
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Def cfg");
		
	}
	function do_exec_no_sub_interface(){
	}
	function get_php_mailer(){
		$mailerman=$this->mainap->get_submanager("sysmail");
		$phpmailer=$mailerman->new_phpmailer();
		$this->cfg_phpmailer($phpmailer);
		return $phpmailer;
	}
	function cfg_phpmailer($phpmailer){
		//	
	}
	function get_php_mailer_info($phpmailer){
		return mw_convert_object_to_array($phpmailer);
	}
	function get_data_man(){
		return $this->__get_priv_testDataMan();
	}
	final function __get_priv_testDataMan(){
		if(!isset($this->testDataMan)){
			if(!$this->testDataMan=$this->createTestDataMan()){
				$this->testDataMan=false;	
			}
		}
		return $this->testDataMan;
	}
	function createTestDataMan(){
		return new mwmod_mw_data_man("maildebug");	
	}
	function do_exec_page_in_proc_input(){
		$input=new mwmod_mw_helper_inputvalidator_request("test");
		if(!$input->is_req_input_ok()){
			return false;	
		}
		
		$createmailer=false;
		if($content=$input->get_value_by_dot_cod_as_list("content")){
			$this->contentInput=$content;
		}
		
		
		if($input->get_value_by_dot_cod("op.savecontent")){
			if($dm=$this->get_data_man()){
				//mw_array2list_echo($dm->get_debug_data(),"dm");
				if($di=$dm->getItemXML("content","test")){
					if($content){
						$di->set_data($content);
						$di->save();	
					}
				}
			}
			
		}
		if($input->get_value_by_dot_cod("op.send")){
			$createmailer=true;
		}
		if($input->get_value_by_dot_cod("op.showinfo")){
			$createmailer=true;
		}
		if($createmailer){
			$phpmailer=$this->get_php_mailer();	
		}
		if($input->get_value_by_dot_cod("op.showinfo")){
			mw_array2list_echo($this->get_php_mailer_info($phpmailer),"MAILER DATA");
		}
		if($input->get_value_by_dot_cod("op.send")){
			if($content){
				echo "<hr>";
				$phpmailer->msgHTML("<!doctype html>".$content["html"]);
				if($content["plain"]){
					$phpmailer->AltBody=$content["plain"];
				}
				$phpmailer->addAddress($content["to"]);
				$phpmailer->Subject=$content["subject"];
				$phpmailer->SMTPDebug=4;
				$phpmailer->Debugoutput="html";
				if($phpmailer->send()){
					echo "<p>ok</p>";
				}else{
					echo "<p>error ".nl2br($phpmailer->ErrorInfo)."</p>";
				}
			}
		}
		
	}
	function do_exec_page_in(){
		//contentInput
		$this->do_exec_page_in_proc_input();
		if(!$this->contentInput){
			if($dm=$this->get_data_man()){
				if($di=$dm->getItemXML("content","test")){
					$this->contentInput=$di->get_data();		
				}
			}
				
		}
		
		//mw_array2list_echo($mailerman->get_cfg_data());
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="test";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("content"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("to","to"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("subject","subject"));
		$i=$gr->add_item(new mwmod_mw_datafield_textarea("plain","plain"));
		$i=$gr->add_item(new mwmod_mw_datafield_textarea("html","html"));
		if($this->contentInput){
			$gr->set_value($this->contentInput);	
		}
		
		$gr=$cr->add_item(new mwmod_mw_datafield_group("op"));
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("send","send"));
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("showinfo","showinfo"));
		$i=$gr->add_item(new mwmod_mw_datafield_checkbox("savecontent","savecontent"));
		
		$submit=$cr->add_submit("Enviar");
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();
		$this->do_exec_page_in_bot();
		echo "<p>IP: ".$_SERVER['SERVER_ADDR']."</p>";
		
		
		
	}
	function do_exec_page_in_bot(){
			
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

	
}
?>