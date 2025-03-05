<?php
class mwmod_mw_ap_apbase  extends mwmod_mw_ap_apabs{
	//creción de submanager, puede extenderse para personalizar falta cambiar classes!!!
	
	function create_submanager_modulesman(){
		$man=new mwmod_mw_modulesman_mainman($this);
		return $man;	
	}
	function create_submanager_devextreme(){
		$man=new mwmod_mw_devextreme_man($this);
		return $man;	
	}
	
	function create_submanager_dateman(){
		$man=new mwmod_mw_date_man($this);
		return $man;	
	}
	function create_submanager_jobs(){
		$man=new mwmod_mw_jobs_jobsmainman($this);
		return $man;	
	}
	
	function create_submanager_imgman(){
		$man=new mwmod_mw_helper_img_imgman($this);
		return $man;	
	}
	function create_submanager_mimetypes(){
		$man=new mwmod_mw_helper_mimetype($this);
		return $man;	
	}
	function create_submanager_sysmail(){
		
		$man=new mwmod_mw_mail_mailer_man_system($this);
		return $man;	
	}
	function create_submanager_bruteforce(){
		
		$man=new mwmod_mw_bruteforce_man($this);
		return $man;	
	}
	
	function create_submanager_captcha(){
		
		$man=new mwmod_mw_helper_captcha_main();
		return $man;	
	}
	
	function create_user_manager(){
		return $this->get_submanager("user");
	}

	function create_submanagerbydefmethod($cod){
		$subman=false;
		if(!$incfile=$this->pathman["instance"]->get_file_path_if_exists($cod.".php","managers")){
			if(!$incfile=$this->pathman["system"]->get_file_path_if_exists($cod.".php","managers")){
				return false;	
			}
		}
		//echo $incfile."<br>";
		ob_start();
		include $incfile;
		ob_end_clean();
		return $subman;
		//include 
		
		//inclyue file que da valor a $subman;	
	}
	/*
	function create_submanager_admininterface(){
		$man=new mw_baseobjects_admininterface($this);
		return $man;	
	}
	function create_submanager_pagesmanager(){
		$man=new mw_baseobjects_pagesmanager($this);
		return $man;	
	}
	*/
	
}

?>