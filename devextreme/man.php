<?php

class mwmod_mw_devextreme_man extends mw_apsubbaseobj{
	function __construct($ap){
		$this->set_mainap($ap);
	}
	function __accepts_exec_cmd_by_url(){
		return true;	
	}
	function exec_getcmd_export($params=array(),$filename=false){
		if(!$filename){
			$filename="data.xlsx";
		}
		ob_end_clean();
		
		if(!empty($_POST["data"]) && !empty($_POST["contentType"]) && !empty($_POST["fileName"])) {
			header("Access-Control-Allow-Origin: *");
			header("Content-type: {$_POST[contentType]};\n");
			header("Content-Transfer-Encoding: binary");
			header("Content-length: ".strlen($_POST["data"]).";\n");
			header("Content-disposition: attachment; filename=\"{$_POST[fileName]}\"");
			die(base64_decode($_POST["data"]));
		} 
	
		header("Content-disposition: attachment; filename=\"error.txt\"");
		die("error");
		
	}

	
}
?>