<?php
class mwmod_mw_mail_mailer_man_systemwithqueue extends mwmod_mw_mail_mailer_man_withqueue{
	function __construct($ap){
		$this->set_mainap($ap);	
	}
	function load_cfg_data(){
		if($file=$this->mainap->get_file_path_if_exists("sysmail.php","cfg","instance")){
			ob_start();
			include $file;
			ob_end_clean();
			if(is_array($data)){
				return $data;
			}
		}
	}
	function setupPhpMailer($phpmailer){
		if($file=$this->mainap->get_file_path_if_exists("setupphpmailer.php","cfg/sysmail","instance")){
			include $file;
		}
		
		
	}
	
	
}
?>