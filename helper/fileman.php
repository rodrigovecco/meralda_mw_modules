<?php

class mwmod_mw_helper_fileman extends mw_apsubbaseobj{
	private $not_allowed_exts;
	private $allowed_exts;
	private $allowed_img_exts;
	var $_check_uploaded_file_info=array();
	
	private $_last_upload_file_info=array();
	function __construct(){
		$this->set_mainap();
	}
	function get_init_allowed_img_exts(){
		//falta que permita jpeg
		$r=array("jpg","gif","png");
		return $r;
	}
	function get_init_allowed_exts(){
		if($r=$this->mainap->cfg->get_value_as_list_no_blank("upload_allowed_extensions")){
			return $r;	
		}
		//debe intentar tomarlas de cfg de aap principal.
		$r=array("pdf","doc","docx","jpg","xls","xlsx","gif","png","swf","mp4","ppt","pptx","txt","rtf","avi","mp3","mp4");
		return $r;
	}
	function get_init_notallowed_exts(){
		return array("htaccess","php","php5","php3","php4","phtml","pl","py","jsp","asp","shtml","sh","cgi");	
	}
	final function reset_last_upload_file_info(&$result=array()){
		$result=array();
		$this->_last_upload_file_info=&$result;	
	}
	final function append_lastupload_file_info($info){
		if(!is_array($info)){
			return false;	
		}
		foreach($info as $k=>$v){
			$this->_last_upload_file_info[$k]=$v;
		}
	}

	
	function check_uploaded_file_multiple($inputname,$newfilename=false,$isimg=false,$urlsercurefilename=true,$valid_exts=false){
		if(!$this->is_multiple($inputname)){
			return false;	
		}
		if(!$info=$this->get_uploaded_file_info_multiple($inputname,$newfilename,$isimg,$urlsercurefilename,$valid_exts)){
			return false;	
		}
		$r=array();
		foreach($info as $i=>$d){
			if(is_array($d)){
				if($d["_allowed_"]){
					$r[]=$d;	
				}
			}
		}
		if(sizeof($r)){
			return $r;	
		}
		
			
	}
	function get_uploaded_file_info_multiple($inputname,$newfilename=false,$isimg=false,$urlsercurefilename=true,$valid_exts=false){
		if(!$this->is_multiple($inputname)){
			return false;	
		}
	
		
		if(!$inputname){
			return false;	
		}
		if(!is_string($inputname)){
			return false;	
		}
		if(!is_array($_FILES)){
			return false;	
		}
		if(!is_array($_FILES[$inputname])){
			return false;
		}
		$r=$_FILES[$inputname];
		$rr=array();
		$i=0;
		foreach($r as $cod=>$d){
			foreach($d as $i=>$v){
				$rr[$i][$cod]=$v;	
			}
		}
		reset($rr);
		$rrr=array();
		foreach($rr as $i=>$d){
			if($dd=$this->get_uploaded_file_info_extended($d,$newfilename,$isimg,$urlsercurefilename,$valid_exts)){
				$rrr[]=$dd;	
			}
		}
		if(sizeof($rrr)){
			return $rrr;
		}
	}

	function is_multiple($inputname){
		if(!$inputname){
			return false;	
		}
		if(!is_array($_FILES[$inputname])){
			return false;	
		}
		if(is_array($_FILES[$inputname]["name"])){
			return true;	
		}
	}
	
	function process_upload_input_delete($input,$filename,$path){
		if(!is_array($input)){
			return false;	
		}
		if(!$filename){
			return false;	
		}
		if(!$path){
			return false;	
			
		}
		if(!is_dir($path)){
			return false;	
		}
		if(!$input["delete"]){
			return false;	
		}
		if(!$fn=basename($filename)){
			return false;	
		}
		if(!$this->check_ext_from_filename($filename)){
			return false;
		}
		$fp=$path."/".$fn;
		if(!is_file($fp)){
			return false;
		}
		if(file_exists($fp)){
			unlink($fp);	
			return true;
		}
		
	}
	
	
	function process_upload_input($input,$path,$deleteifexists=false,$isimg=false,$newfilename=false,$replace=true,$urlsercurefilename=true,$valid_exts=false){
		if(!is_array($input)){
			return false;	
		}

		if(!$inputname=$input["fileinputname"]??null){
			return false;	
		}
		
		return $this->upload_file($inputname,$path,$deleteifexists,$isimg,$newfilename,$replace,$urlsercurefilename,$valid_exts);
		
	}
	function check_process_upload_input($input,$path,$deleteifexists=false,$isimg=false,$newfilename=false,$replace=true,$urlsercurefilename=true,$valid_exts=false,&$info=array(),&$invaliduploadattempt=false){
		$invaliduploadattempt=false;
		if(!$info=$this->check_process_upload_input_get_info($input,$path,$deleteifexists,$isimg,$newfilename,$replace,$urlsercurefilename,$valid_exts)){
			$info=array();
		}
		if($info["result"]??null){
			return true;
		}else{
			if($info["uploadattempt"]??null){
				$invaliduploadattempt=true;
			}
			return false;
				
		}
		
		
	}
	function check_process_upload_input_get_info($input,$path,$deleteifexists=false,$isimg=false,$newfilename=false,$replace=true,$urlsercurefilename=true,$valid_exts=false){
		$info=array();
		$info["result"]=false;
		$info["invalidfile"]=false;
		$info["uploadattempt"]=false;
		$info["error"]=false;
		//if(!$info["_allowed_"]){
		
		if(!is_array($input)){
			$info["error"]="invalid_input";
			return $info;	
		}
		if(!$inputname=$input["fileinputname"]??null){
			$info["error"]="invalid_input";
			return $info;	
		}
		if(!is_string($inputname)){
			$info["error"]="invalid_input";
			return $info;	
		}
		if(!is_array($_FILES)){
			$info["error"]="invalid_input";
			
			return $info;	
		}
		if(!is_array($_FILES[$inputname]??null)){
			return $info;	
		}
		if($name=trim($_FILES[$inputname]["name"])){
			$info["uploadattempt"]=true;
		}

		
		if(!$extrainfo=$this->get_uploaded_file_info($inputname,$newfilename,$isimg,$urlsercurefilename,$valid_exts)){
			return $info;	
		}
		$info["extra"]=$extrainfo;
		$info["result"]=$extrainfo["_allowed_"];
		return $info;
	}
	
	function check_and_create_path($path){
		if(!$path){
			return false;	
		}
		if(!is_string($path)){
			return false;	
		}
		if(!$this->create_dir($path)){
			return false;
		}
		return realpath($path);
		
		
	}
	function upload_file($inputname,$path,$deleteifexists=false,$isimg=false,$newfilename=false,$replace=true,$urlsercurefilename=true,$valid_exts=false){
		if(!$info=$this->check_uploaded_file($inputname,$newfilename,$isimg,$urlsercurefilename,$valid_exts)){
			return false;	
		}
		if(!$path=$this->check_and_create_path($path)){
			return false;
		}
		$valid_exts=$this->get_valid_exts($valid_exts,$isimg);
		
		$nfilename=$info["_name_new_full"];
		$nfp=$path."/".$nfilename;
		if(file_exists($nfp)){
			if($replace){
				
				unlink($nfp);	
			}else{
				return false;	
			}
		}

		
		if($deleteifexists){
			if($deleteifexists=basename($deleteifexists)){
				$fp=$path."/".$deleteifexists;
				if($this->check_ext_from_filename($deleteifexists,$valid_exts)){
					if(is_file($fp)){
						if(file_exists($fp)){
							
							unlink($fp);	
						}
					}
				}
				
			}
		}
		
		$tmp_name=$info["tmp_name"];
		if(!move_uploaded_file($tmp_name, $nfp)){
			return false;	
		}
		return $nfilename;

	}
	
	function check_uploaded_file($inputname,$newfilename=false,$isimg=false,$urlsercurefilename=true,$valid_exts=false,&$result=false){
		if(!$info=$this->get_uploaded_file_info($inputname,$newfilename,$isimg,$urlsercurefilename,$valid_exts)){
			return false;	
		}
		$result=$info;
		if(!$info["_allowed_"]){
			return false;	
		}
		return $info;
		
	}
	function get_uploaded_file_info_extended($info,$newfilename=false,$isimg=false,$urlsercurefilename=true,$valid_exts=false){
		if(!is_array($info)){
			return false;	
		}
		$r=$info;
		$r["_allowed_"]=false;
		$r["_extok_"]=false;
		$r["invalid_ext"]=false;
		
		$valid_exts=$this->get_valid_exts($valid_exts,$isimg);
		if(!$name=trim($info["name"])){
			$this->append_lastupload_file_info($r);
			return false;
		}
		$r["_name"]=$name;
		if(!$origname=$this->get_filenamenoext($name)){
			$this->_check_uploaded_file_info[]=$r;
			$this->append_lastupload_file_info($r);
			return $r;
		}
		$r["_name_orig"]=$origname;
		$newname=$origname;
		
		if($newfilename){
			if($newfilename=basename($newfilename)){
				$newname=$newfilename;
			}
		}
		if($urlsercurefilename){
			if(!$newname=$this->get_url_secure_filename($newname)){
				$this->_check_uploaded_file_info[]=$r;
				$this->append_lastupload_file_info($r);
				return $r;
						
			}
		}
		
		$r["_name_new"]=$newname;
		if(!$ext=$this->get_ext($name)){
			$this->_check_uploaded_file_info[]=$r;
			$this->append_lastupload_file_info($r);
			return $r;
				
		}
		$r["ext"]=$ext;
		if($info["error"]){
			$this->_check_uploaded_file_info[]=$r;
			$this->append_lastupload_file_info($r);
			return $r;
				
		}
		
		if($this->check_ext($ext,$valid_exts)){
			$r["_allowed_"]=true;
			$r["_extok_"]=true;

		}else{
			$r["invalid_ext"]=$ext;	
		}
		$r["_name_new_full"]=$newname.".".$ext;
		$this->_check_uploaded_file_info[]=$r;
		$this->append_lastupload_file_info($r);
		return $r;
	}
	function get_url_secure_filename_noext($file){
		$f=$this->get_filenamenoext($file);
		return $this->get_url_secure_filename($f);	
	}
	function get_url_secure_filename($file){
		if(!$file){
			return false;	
		}
		if(!$file=basename($file)){
			return false;
		}
		$file=mw_text_replace_lngchars($file);
		
		
		$file=strtolower($file);
		$num=strlen($file);
		$str1="";
		for ($x=0;$x<$num;$x++){
			$ch=substr($file,$x,1);
			if(!ctype_alnum($ch)){
				$ch="_";	
			}
			$str1.=$ch;
		}
		if($str1){
			$str1=str_replace("__","_",$str1);
			$str1=str_replace("__","_",$str1);
			return $str1;	
		}
		return false;
		
			
	}
	
	function get_filenamenoext($file){
		if(!$file){
			return false;	
		}
		if(!$file=basename($file)){
			return false;
		}
		
		if(!$ii=pathinfo($file)){
			return false;	
		}
		return $ii["filename"];

	}

	function get_uploaded_file_info($inputname,$newfilename=false,$isimg=false,$urlsercurefilename=true,$valid_exts=false){
		if(!$inputname){
			return false;	
		}
		if(!is_string($inputname)){
			return false;	
		}
		if(!is_array($_FILES)){
			return false;	
		}
		if(!is_array($_FILES[$inputname]??null)){
			return false;
		}
		$r=$_FILES[$inputname];
		$r["_inputname"]=$inputname;
		$this->append_lastupload_file_info($r);
		return $this->get_uploaded_file_info_extended($r,$newfilename,$isimg,$urlsercurefilename,$valid_exts);
	}
	
	
	
	
	function check_sub_dir($dir){
		if(!$dir){
			return false;	
		}
		if(!is_string($dir)){
			return false;
		}
		if(strpos($dir," ")!==false){
			return false;	
		}
		$dir=str_replace("\\","/",$dir);
		$dir=str_replace("//","/",$dir);
		$dir=str_replace("//","/",$dir);
		$dir=str_replace("//","/",$dir);
		$dir=trim($dir,"/");
		if(!$dir){
			return false;	
		}
		if($dir=="/"){
			return false;	
		}
		if(strpos($dir,".")!==false){
			return false;	
		}
		return $dir;
		
	}
	function create_sub_dir($mainpath,$subdir,$chmod=0755){
		if(!$subdir=$this->check_sub_dir($subdir)){
			return false;	
		}
		$dir=$mainpath."/".$subdir;
		if(is_dir($dir)){
			return true;	
		}
		if(!is_dir($mainpath)){
			return true;	
		}
		$l=explode("/",$subdir);
		$nd=$mainpath;
		foreach ($l as $s){
			$nd.="/".$s;
			if(!is_dir($nd)){
				if (!@mkdir($nd,$chmod)){
					return false;
				}
				chmod($nd,$chmod);
			}
		}
		return true;	
	}
	
	private function _create_dir($dir,$chmod=0755){
		if(!$root=$this->mainap->get_path("root")){
			return false;
		}
		$rootlen=strlen($root);
		if(substr($dir,0,$rootlen)!==$root){
			return false;	
		}
		if(!$sdir=substr($dir,$rootlen+1)){
			return false;	
		}
		return $this->create_sub_dir($root,$sdir,$chmod);
	
	}

	
	final function create_dir($dir,$chmod=0755){
		if(!$dir){
			return false;	
		}
		if(!is_string($dir)){
			return false;
		}
		if(is_dir($dir)){
			return true;	
		}
		return $this->_create_dir($dir,$chmod);
	}

	
	function get_valid_exts(&$valid_exts=false,$isimg=false){
		if(!is_array($valid_exts)){
			if($isimg){
				$valid_exts=$this->get_allowed_img_exts();
			
			
			}else{
				$valid_exts=$this->get_allowed_exts();
			}
		}
		return $valid_exts;
		
	}
	function check_ext_from_filename($file,$valid_exts=false,$check_not_allowed=true){
		if(!$ext=$this->get_ext($file)){
			return false;	
		}
		return $this->check_ext($ext,$valid_exts,$check_not_allowed);
			
	}
	function check_ext($ext,$valid_exts=false,$check_not_allowed=true){
		if(!$ext){
			return false;	
		}
		if(!is_string($ext)){
			return false;
		}
		
		if(!is_array($valid_exts)){
			$valid_exts=$this->get_allowed_exts();
		}
		if($check_not_allowed){
			$not_valid_exts=$this->get_not_allowed_exts();	
			if(in_array($ext,$not_valid_exts)){
				return false;	
			}
		}
		
		
		if(in_array($ext,$valid_exts)){
			return $ext;	
		}
	}
	function get_ext($file){
		if(!$file=basename($file)){
			return false;	
		}
		
		if (!strpos($file,".")){
			return false;
		}
		$newfileexs= explode (".",$file);
		if(sizeof($newfileexs)<=1){
			return false;	
		}
		if(!$ext=array_pop($newfileexs)){
			return false;	
		}
		return strtolower($ext);
	}
	function is_dir($path){
		if(!$path){
			return false;	
		}
		
		if(is_link($path)){
			return false;	
		}
		if(is_file($path)){
			return false;
		}
		if(is_dir($path)){
			return true;	
		}
		
			
	}
	function get_sub_dirs($path){
		if(!$this->is_dir($path)){
			return false;	
		}
		if(!$list=scandir($path)){
			return true;	
		}
		$r=array();
		foreach($list as $file){
			$subpath=$path."/".$file;
			if(($file!=".")and($file!="..")){
				if($this->is_dir($subpath)){
					$r[$subpath]=$file;
				}
			}
		}
		return $r;
		
	}

	function delete_path($path){
		if(is_link($path)){
			return false;	
		}
		if(is_file($path)){
			return false;
		}
		if(!is_dir($path)){
			return true;	
		}
		if(!$list=scandir($path)){
			rmdir($path);
			return true;	
		}
		$notallowedexts=$this->get_not_allowed_exts();
		foreach($list as $file){
			$subpath=$path."/".$file;
			if(($file!=".")and($file!="..")){
				if(is_link($subpath)){
					return false;
				}
				if(is_file($subpath)){
					
					if(!$ext=$this->get_ext($file)){
						return false;
					}
					if(in_array($ext,$notallowedexts)){
						return false;
					}
					
					unlink($subpath);
					
				}elseif(!$this->delete_path($subpath)){
					
					return false;
				}
		
			}
		}
		
		rmdir($path);
		return true;	
		mw_array2list_echo($list);
	}

	
	final function get_not_allowed_exts(){
		if(!isset($this->not_allowed_exts)){
			$this->not_allowed_exts=$this->get_init_notallowed_exts();
		}
		return $this->not_allowed_exts;
	}
	final function get_allowed_img_exts(){
		if(!isset($this->allowed_img_exts)){
			$this->allowed_img_exts=$this->get_init_allowed_img_exts();
		}
		return $this->allowed_img_exts;
	}
	final function get_allowed_exts(){
		if(!isset($this->allowed_exts)){
			$this->allowed_exts=$this->get_init_allowed_exts();
		}
		return $this->allowed_exts;
	}
	
	
}
?>