<?php
abstract class mwmod_mw_ui_main_uimainabsajax extends mwmod_mw_ui_main_uimainabs{
	
	var $sxml_add_debug_info=false;
	function exec_getcmd_sessioncheck($params=array(),$filename=false){
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		$ok=false;
		$xml->set_prop("notify.type","warning");
		$xml->set_prop("notify.message",$this->lng_common_get_msg_txt("session_has_expired","La sesión ha expirado"));	
		$xml->set_prop("gotourl",$this->get_relogin_url());
		if($user=$this->get_admin_current_user()){
			if($id=$user->get_id()){
				$xml->set_prop("user.ok",true);
				$xml->set_prop("user.id",$id);
				$xml->set_prop("user.idname",$user->get_idname());
				$xml->set_prop("user.name",$user->get_real_name());
				$ok=true;
			}
		}
		if(!$ok){
			$xml->set_prop("user.ok",false);
		}
		$xml->root_do_all_output();

			
	}
	
	function get_exec_cmd_dl_url_from_ui_full_cod($dlcmd,$ui_full_cod,$params=array(),$realfilename=false){
		//$ui_full_cod sep musbt by -
		$p=array();
		$p["ui"]="";
		if($ui_full_cod){
			if(is_string($ui_full_cod)){
				$p["ui"]=$ui_full_cod;
			}
		}
		if(is_array($params)){
			foreach($params as $cod=>$pp){
				$p[$cod]=$pp;	
			}
		}
		if(!$dlcmd){
			return $this->get_exec_cmd_url("dl",$p,false);	
		}
		$filename=$dlcmd;
		
		if($realfilename){
			$filename.=".".$realfilename;	
		}
		
		return $this->get_exec_cmd_url("dl",$p,$filename);
	}
	
	function get_exec_cmd_sxml_url_from_ui_full_cod($xmlcmd,$ui_full_cod,$params=array()){
		$p=array();
		$p["ui"]="";
		if($ui_full_cod){
			if(is_string($ui_full_cod)){
				$p["ui"]=$ui_full_cod;
			}
		}
		if(is_array($params)){
			foreach($params as $cod=>$pp){
				$p[$cod]=$pp;	
			}
		}
		if(!$xmlcmd){
			return $this->get_exec_cmd_url("sxml",$p,false);	
		}
		$filename=$xmlcmd.".xml";
		return $this->get_exec_cmd_url("sxml",$p,$filename);
	}
	function get_exec_cmd_sxml_url($xmlcmd,$sub_ui,$params=array()){
		
		$ui_full_cod=$sub_ui->get_full_cod("-");
		return $this->get_exec_cmd_sxml_url_from_ui_full_cod($xmlcmd,$ui_full_cod,$params);
	}
	
	
	function get_exec_cmd_dl_url($dlcmd,$sub_ui,$params=array(),$realfilename=false){
		$ui_full_cod=$sub_ui->get_full_cod("-");
		return $this->get_exec_cmd_dl_url_from_ui_full_cod($dlcmd,$ui_full_cod,$params,$realfilename);
		
		
		
	}
	function exec_getcmd_dl_not_allowed($params=array(),$filename=false){
		return false;
	}
	function exec_getcmd_dl($params=array(),$filename=false){
		if(!is_array($params)){
			return $this->exec_getcmd_dl_not_allowed($params,$filename);	
		}
		if(!$this->dl_cmd_ok()){
			$nouser_ok=false;
			if($sub_ui=$this->set_current_subinterface_for_getcmd($params["ui"])){
				if($sub_ui->is_allowed_for_get_cmd_no_user()){
					$nouser_ok=true;	
				}
			}
			if(!$nouser_ok){
				return $this->exec_getcmd_dl_not_allowed($params,$filename);	
			}
		}
		if(!$sub_ui=$this->set_current_subinterface_for_getcmd($params["ui"],$params,$filename)){
			return $this->exec_getcmd_dl_not_allowed($params,$filename);		
		}
		$filename=$filename."";
		$f=explode(".",$filename."",2);
		$fn=$f[1]??"";
		if(!strpos($fn,".")){
			$fn=$filename;
		}
		return $sub_ui->execfrommain_getcmd_dl($f[0],$params,$fn);
			
	}
	
	
	function set_current_subinterface_for_getcmd($cods,$params=array(),$filename=false){
		//$cods str sep by -	
		if(!$cods){
			return false;	
		}
		if(!is_string($cods)){
			return false;	
		}
		$cods=explode("-",$cods,2);
		if(!$sub_ui=$this->get_subinterface($cods[0])){
			return false;	
		}
		if(!$sub_ui->is_allowed_for_get_cmd($cods[1]??null,$params,$filename)){
			return false;	
		}
		if(!$this->set_current_subinterface($sub_ui)){
			return false;	
		}
		$cods1=$cods[1]??null;
		if(!$cods1){
			return $sub_ui;	
		}
		return $sub_ui->set_current_subinterface_for_getcmd($cods[1],$params,$filename);
		
		//is_allowed_for_get_cmd
		
		
	}
	function exec_getcmd_sxml($params=array(),$filename=false){
		if(!$this->sxml_cmd_ok()){
			return $this->exec_getcmd_sxml_not_allowed($params,$filename);	
		}
		if(!is_array($params)){
			return $this->exec_getcmd_sxml_not_allowed($params,$filename);	
		}
		if(!$sub_ui=$this->set_current_subinterface_for_getcmd($params["ui"],$params,$filename)){
			return $this->exec_getcmd_sxml_not_allowed($params,$filename);		
		}
		if(!$filename){
			return $this->exec_getcmd_sxml_not_allowed($params,$filename);		
		}
		$f=explode(".",$filename."");
		return $sub_ui->execfrommain_getcmd_sxml($f[0],$params,$filename);
			
	}
	function get_new_sxml_data($params=array(),$filename=false,$add_debug_info=false){
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		$xml->set_prop("ok",false);
		$xml->set_prop("msg",$this->lng_common_get_msg_txt("not_allowed","No permitido"));
		if($add_debug_info){
			$xmldebug=$xmlroot->get_sub_root("debug");
			$xmldebug->set_prop("params",$params);
			$xmldebug->set_prop("filename",$filename);
			
		}
		return $xml;
			
	}
	function exec_getcmd_sxml_not_allowed($params=array(),$filename=false){
		$xml=$this->get_new_sxml_data($params,$filename,$this->sxml_add_debug_info);
		$xmlroot=$xml->get_root();
		$xmlroot->xml_output_start();
		$xmlroot->output_all();
		
			
	}
	function sxml_cmd_ok(){
		return $this->admin_user_ok();	
	}
	function dl_cmd_ok(){
		return $this->admin_user_ok();	
	}

	function __accepts_exec_cmd_by_url(){
		return true;	
	}
	
}
?>