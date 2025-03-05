<?php
class mwmod_mw_extmods_qr extends mw_apsubbaseobj{
	public $extLibsIncluded;
	function __construct(){
		//
	}
	function newQr($data, $options = []){
		if(!$this->include_ext_libs()){
			return false;
		}
		return new QRCode($data,$options);
	}
	function include_ext_libs(){
		if(isset($this->extLibsIncluded)){
			return $this->extLibsIncluded;
		}
		$this->extLibsIncluded=false;

		$subpathman=$this->mainap->get_sub_path_man("modulesext/qrcode","system");
		if($fullpath=$subpathman->get_file_path_if_exists("qrcode.php")){
			//echo $fullpath;
			require_once $fullpath;
			if(class_exists("QRCode",false)){
				$this->extLibsIncluded=true;
				
			
			}

		}
		return $this->extLibsIncluded;
	}
}

?>