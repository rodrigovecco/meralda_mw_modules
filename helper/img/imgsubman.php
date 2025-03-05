<?php

class mwmod_mw_helper_img_imgsubman extends mw_apsubbaseobj{
	private $mainman;
	private $_proccess_info;
	private $_src_data;
	private $cod="img";
	public $img_path;
	public $current_filename;
	var $cfg=array();
	private $_crop_options;
	function __construct($mainman,$cod=false){
		$this->init($mainman,$cod);	
	}
	final function unset_src(){
		unset($this->_src_data);	
	}
	function set_crop_options_from_input($options){
		if(!$options){
			return false;	
		}
		if(is_array($options)){
			return $this->set_crop_options($options);	
		}
		if(!is_string($options)){
			return false;
		}
		if(!$options=trim($options)){
			return false;	
		}
		if (empty($options)) {
			return false;
		}
		if(strpos($options,"{")!==false){
			$r=json_decode(stripslashes($options),true);
			return $this->set_crop_options($r);
		}
		if(strpos($options,"|")!==false){
			$pairs=explode("|",$options);
			$r=array();
			foreach($pairs as $pair){
				if($pair=trim($pair)){
					$pair_a=explode(":",$pair);
					if($cod=trim($pair_a[0])){
						$r[$cod]=$pair_a[1]+0;	
					}
				}
			}
			return $this->set_crop_options($r);	
		}
		
		
	}
	final function set_crop_options($options){
		$this->_crop_options=false;
		if(!$options){
			return false;	
		}
		if(!is_array($options)){
			return false;	
		}
		if(!$options["width"]=$options["width"]+0){
			return false;	
		}
		if(!$options["height"]=$options["height"]+0){
			return false;	
		}
		$options["x"]=$options["x"]+0;
		$options["y"]=$options["y"]+0;
		$options["rotate"]=$options["rotate"]+0;
		
		$this->_crop_options=$options;
		return true;
	}
	final function get_crop_options(){
		if(!is_array($this->_crop_options)){
			return false;	
		}
		return $this->_crop_options;
	}
	function after_upload($filename,$path){
		if($new=$this->crop($filename,$path)){
			if(is_string($new)){
				$filename=$new;	
			}
		}
		
		
		return $this->transform_file($filename,$path);	
		
	}
	function crop($filename,$path){
		//falta rotate
		if(!$crop=$this->get_crop_options()){
			return false;
		}
		
		if(!$filename=basename($filename)){
			return false;	
		}
		if(!is_dir($path)){
			return false;
		}
		$fp=$path."/".$filename;
		$ok=false;
		$width=false;
		$height=false;
		$mime=false;
		$ext=false;
		$namenoext=false;
		
		if(!$info=$this->get_file_info($fp,$ok,$width,$height,$mime,$ext,$namenoext)){
			return false;
		}
		$transp=-1;
		$src_img=false;
		$mode=$ext;
		if($ext=="jpg"){
			$src_img=imagecreatefromjpeg($fp);
		}elseif($ext=="gif"){
			$src_img=imagecreatefromgif($fp);
			$transp=imagecolortransparent($src_img);
		
		}elseif($ext=="png"){
			$src_img=imagecreatefrompng($fp);
			$transp=imagecolortransparent($src_img);
		}else{
			return false;	
		}
		if(!$src_img){
			return false;	
		}
		if($mode=="gif"){
			$dest_img=imagecreate(round($crop["width"]), round($crop["height"]));
		}else{
			$dest_img = imagecreatetruecolor(round($crop["width"]), round($crop["height"]));	
		}
		if($transp!=-1){  
			$colortransp= imagecolorsforindex($src_img, $transp);
			$idColorTransp = imagecolorallocatealpha($dest_img, $colortransp['red'], $colortransp['green'], $colortransp['blue'], $colortransp['alpha']); 
			imagefill($dest_img, 0, 0, $idColorTransp);
			imagecolortransparent($dest_img, $idColorTransp);
			imagealphablending($dest_img, false);
			imagesavealpha($dest_img, true);
		}elseif($mode=="png"){
			imagealphablending($dest_img, false);
			imagesavealpha($dest_img, true);
		}
		if(!imagecopyresampled($dest_img, $src_img, 0,0, round($crop["x"]), round($crop["y"]), round($crop["width"]), round($crop["height"]), round($crop["width"]), round($crop["height"]))){
			imagedestroy($src_img);
			imagedestroy($dest_img);
			return false;
		}
		if(!$namenoext=basename($namenoext)){
			return false;	
		}
		if($mode=="gif"){
			$new_filename=$namenoext.".gif";
		}elseif($mode=="png"){
			$new_filename=$namenoext.".png";
		}elseif($mode=="jpg"){
			$new_filename=$namenoext.".jpg";
		}else{
			imagedestroy($src_img);
			imagedestroy($dest_img);
			return false;	
		}
		$fp_dest=$path."/".$new_filename;
		

		if(is_file($fp_dest)){
			if(file_exists($fp_dest)){
				unlink($fp_dest);	
			}
		}
		if($mode=="gif"){
			imagegif($dest_img,$fp_dest);
		}elseif($mode=="png"){
			imagepng($dest_img,$fp_dest);
		}elseif($mode=="jpg"){
			imagejpeg($dest_img,$fp_dest,100);
		}else{
			return false;	
		}
		
		
		imagedestroy($src_img);
		imagedestroy($dest_img);
		return $new_filename;
		
	}
	
	function delete(){
		if(!$path=$this->img_path){
			return false;	
		}
		if($old=basename($this->current_filename??"")){
			if($this->check_ext_from_filename($old)){
				$oldfull=$path."/".$old;
				if(is_file($oldfull)){
					if(file_exists($oldfull)){
						unlink($oldfull);
						return true;
					}
				}
			}
				
		}
			
	}
	function copy_from_file($srcfile){
		
		if(!$this->set_src($srcfile)){
			return false;	
		}
		
		if(!$pinfo=$this->set_proccess_info_from_file($srcfile)){
			//return $filename;
			return false;
		}
		if(!$path=$this->img_path){
			return false;	
		}
		$this->delete();
		if(!$new=$this->create_new_img_file(basename($srcfile),$path)){
			return false;	
		}
		return $new;
			
	}
	
	function copy_transformed_from_file($srcfile){
		
		if(!$this->set_src($srcfile)){
			return false;	
		}
		
		if(!$pinfo=$this->set_proccess_info_from_file($srcfile)){
			//return $filename;
			return false;
		}
		if(!$path=$this->img_path){
			return false;	
		}
		$this->delete();
		return $this->create_new_img_file(basename($srcfile),$path);
			
	}
	/*
	function move_from_uploaded_file_info($info,$newfilename=false,$replace=true,$urlsercurefilename=true){
		
		//mw_array2list_echo($info);
		$deleteifexists=$this->current_filename;
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		$isimg=true;
		if(!$nf=$fm->move_from_uploaded_file_info($info,$path,$deleteifexists,$isimg,$newfilename,$replace,$urlsercurefilename)){
			return false;	
		}
		return $this->transform_file($nf,$path);
		
	}
	*/

	private  function _set_src($src,$mode,$transp){
		$this->_src_data=array(
			"src"=>$src,
			"mode"=>$mode,
			"transp"=>$transp,
		);
	}
	function create_new_img(){
		if(!$info=$this->get_proccess_info()){
			return false;	
		}
		if(!$srcdata=$this->get_src_data()){
			return false;	
		}
		$transp=$srcdata["transp"];
		$imgsrc=$srcdata["src"];
		$src_width=$info["origdim"]["width"];
		$src_height=$info["origdim"]["height"];
		$des_width=$info["newdim"]["width"];
		$des_height=$info["newdim"]["height"];
		$mode=$srcdata["mode"];
		
		if($mode=="gif"){
			$newimg=ImageCreate($des_width, $des_height);
		}elseif($mode=="png"){
			$newimg=ImageCreatetruecolor($des_width, $des_height);
		}elseif($mode=="jpg"){
			$newimg=ImageCreatetruecolor($des_width, $des_height);
		}else{
			return false;	
		}
		if($transp!=-1){  
			$colortransp= imagecolorsforindex($imgsrc, $transp);
			$idColorTransp = imagecolorallocatealpha($newimg, $colortransp['red'], $colortransp['green'], $colortransp['blue'], $colortransp['alpha']); 
			// Asigna un color en una imagen retorna identificador de color o FALSO o -1 apartir de la version 5.1.3
			imagefill($newimg, 0, 0, $idColorTransp);
			// rellena de color desde una cordenada, en este caso todo rellenado del color que se definira como transparente
			imagecolortransparent($newimg, $idColorTransp);
			 //Ahora definimos que en el nueva imagen el color transparente será el que hemos pintado el fondo.
			imagealphablending($newimg, false);
			imagesavealpha($newimg, true);
		}elseif($mode=="png"){
			imagealphablending($newimg, false);
			imagesavealpha($newimg, true);
		}
		
		imagecopyresampled($newimg, $imgsrc,0,0,0,0, $des_width, $des_height,$src_width,$src_height);
		return $newimg;


	
	}
	function create_new_img_file($filename,$path){
		
		if(!$filename){
			return false;	
		}
		if(!$filename=basename($filename)){
			return false;
		}
		if(!$dest=$this->create_new_img()){
		
			return false;	
		}
		if(!$srcdata=$this->get_src_data()){
			return false;	
		}
		
		$mode=$srcdata["mode"];
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		
		if(!$fnamenoext=$fm->get_url_secure_filename_noext($filename)){
			return false;	
		}
		$new_filename=false;
		
		if($mode=="gif"){
			$new_filename=$fnamenoext.".gif";
		}elseif($mode=="png"){
			$new_filename=$fnamenoext.".png";
		}elseif($mode=="jpg"){
			$new_filename=$fnamenoext.".jpg";
		}else{
			return false;	
		}
		
		
		if(!$fm->create_dir($path)){
			return false;	
		}
		$new_file=$path."/".$new_filename;
		if(file_exists($new_file)){
			unlink($new_file);	
		}
		
		if($mode=="gif"){
			imagegif($dest,$new_file);
		}elseif($mode=="png"){
			imagepng($dest,$new_file);
		}elseif($mode=="jpg"){
			imagejpeg($dest,$new_file,100);
		}else{
			return false;	
		}
		return $new_filename;
	

		
	}
	final function get_src_data(){
		return 	$this->_src_data;
	}
	function set_src($file){
		$this->unset_src();
		if(!file_exists($file)){
			return false;	
		}
		$fn=basename($file);
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		$transp=-1;
		$mode=false;
		if(!$ext=$fm->get_ext($fn)){
			return false;	
		}
		$src=false;
		if($ext=="jpg"){
			$mode="jpg";
			$src=imagecreatefromjpeg($file);
		}elseif($ext=="gif"){
			$mode="gif";
			$src=imagecreatefromgif($file);
			$transp=imagecolortransparent($src);
		
		}elseif($ext=="png"){
			$mode="png";
			$src=imagecreatefrompng($file);
			$transp=imagecolortransparent($src);
		
		}else{
			return false;	
		}
		if(!$src){
			return false;	
		}
		$this->_set_src($src,$mode,$transp);
		
		return true;
	
			
	}
	
	function set_proccess_info_from_array($info){
		$this->unset_proccess_info();
		if(!is_array($info)){
			return false;
		}
		$info["origdim"]=array();
		$info["origdim"]["width"]=$info["width"];
		$info["origdim"]["height"]=$info["height"];
		$info["dimchanged"]=false;
		
		
		if(!$ndim=$this->get_new_dim($info["width"],$info["height"])){
			return false;		
		}
		$info["newdim"]=$ndim;
		if($info["newdim"]["width"]!=$info["origdim"]["width"]){
			$info["dimchanged"]=true;	
		}
		if($info["newdim"]["height"]!=$info["origdim"]["height"]){
			$info["dimchanged"]=true;	
		}
		$this->set_proccess_info($info);
		return true;
		
		
		
	}
	function dim_has_changed(){
		if(!$info=$this->get_proccess_info()){
			return false;	
		}
		return $info["dimchanged"];
		
	}
	function transform_file($filename,$path){
		if(!$filename=basename($filename)){
			return false;	
		}
		if(!is_dir($path)){
			return false;
		}
		$fp=$path."/".$filename;
		if(!$pinfo=$this->set_proccess_info_from_file($fp)){
			return $filename;
		}
		if(!$this->dim_has_changed()){
			return $filename;	
			
		}
		if(!$this->set_src($fp)){
			return $filename;		
		}
		if($newfn=$this->create_new_img_file(basename($fp),dirname($fp))){
			return $newfn;		
		}
		
		
		return $filename;	
		
	}
	function check_ext_from_filename($file){
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		$valid_exts=$fm->get_init_allowed_img_exts();
		return $fm->check_ext_from_filename($file,$valid_exts);
			
	}

	function set_proccess_info_from_file($file){
		$this->unset_proccess_info();
		if(!is_file($file)){
			return false;
		}
		if(!file_exists($file)){
			return false;
		}
		if(!$this->check_ext_from_filename(basename($file))){
			return false;	
		}
		if(!$info=getimagesize($file)){
			return false;
		}
		$r=array(
			"width"=>$info[0],
			"height"=>$info[1],
			"mime"=>$info["mime"]
		);
		return $this->set_proccess_info_from_array($r);
	}
	function get_file_info($file,&$ok=false,&$width=false,&$height=false,&$mime=false,&$ext=false,&$namenoext=false){
		$ok=false;
		$width=false;
		$height=false;
		$mime=false;
		$ext=false;
		$namenoext=false;
		if(!is_file($file)){
			return false;
		}
		if(!file_exists($file)){
			return false;
		}
		if(!$this->check_ext_from_filename(basename($file))){
			return false;	
		}
		if(!$info_raw=getimagesize($file)){
			return false;
		}
		$width=$info_raw[0];
		$height=$info_raw[1];
		$mime=$info_raw["mime"];
		
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		if(!$ext=$fm->get_ext(basename($file))){
			return false;	
		}
		$namenoext=$fm->get_filenamenoext(basename($file));
		$ok=true;
		$info=array(
			"ok"=>$ok,
			"width"=>$width,
			"height"=>$height,
			"mime"=>$mime,
			"ext"=>$ext,
			"namenoext"=>$namenoext,
		);
		return $info;
	}
	//set_new_dim
	//all rel
	function set_new_dim_rel_fix(&$width,&$height){
		$orig=$height;
		$this->set_ndim_fix($height,false);
		$width=$this->get_rel_dim($orig,$height,$width);
		return true;	
	}
	function set_new_dim_rel_min(&$width,&$height){
		$orig=$height;
		$this->set_ndim_min($height,false);
		$width=$this->get_rel_dim($orig,$height,$width);
		return true;	
	}
	function set_new_dim_rel_max(&$width,&$height){
		$orig=$height;
		$this->set_ndim_max($height,false);
		$width=$this->get_rel_dim($orig,$height,$width);
		return true;	
	}
	////
	function set_new_dim_fix_rel(&$width,&$height){
		$orig=$width;
		$this->set_ndim_fix($width,true);
		$height=$this->get_rel_dim($orig,$width,$height);
		return true;	
	}
	function set_new_dim_max_rel(&$width,&$height){
		$orig=$width;
		$this->set_ndim_max($width,true);
		$height=$this->get_rel_dim($orig,$width,$height);
		return true;	
	}
	function set_new_dim_min_rel(&$width,&$height){
		$orig=$width;
		$this->set_ndim_min($width,true);
		$height=$this->get_rel_dim($orig,$width,$height);
		return true;	
	}
	
	//free done
	function set_new_dim_free_free(&$width,&$height){
		return true;	
	}
	function set_new_dim_free_fix(&$width,&$height){
		$this->set_ndim_fix($height,false);
		
		return true;	
	}
	function set_new_dim_free_rel(&$width,&$height){
		return true;	
	}
	function set_new_dim_free_min(&$width,&$height){
		$this->set_ndim_min($height,false);
		
		return true;	
	}
	function set_new_dim_free_max(&$width,&$height){
		$this->set_ndim_max($height,false);
		return true;	
	}
	///
	function set_new_dim_fix_free(&$width,&$height){
		$this->set_ndim_fix($width,true);
		return true;	
	}
	function set_new_dim_fix_fix(&$width,&$height){
		$this->set_ndim_fix($width,true);
		$this->set_ndim_fix($height,false);
		return true;	
	}
	function set_new_dim_fix_min(&$width,&$height){
		$this->set_ndim_fix($width,true);
		$this->set_ndim_min($height,false);
		return true;	
	}
	function set_new_dim_fix_max(&$width,&$height){
		$this->set_ndim_fix($width,true);
		$this->set_ndim_max($height,false);
		return true;	
	}
	///
	function set_new_dim_rel_free(&$width,&$height){
		return true;	
	}
	function set_new_dim_rel_rel(&$width,&$height){
		return true;	
	}
	///
	function set_new_dim_min_free(&$width,&$height){
		$this->set_ndim_min($width,true);
		
		return true;	
	}
	function set_new_dim_min_fix(&$width,&$height){
		$this->set_ndim_min($width,true);
		$this->set_ndim_fix($height,false);
		return true;	
	}
	function set_new_dim_min_min(&$width,&$height){
		$this->set_ndim_min($width,true);
		$this->set_ndim_min($height,false);
		return true;	
	}
	function set_new_dim_min_max(&$width,&$height){
		$this->set_ndim_min($width,true);
		$this->set_ndim_max($height,false);
		return true;	
	}
	///
	function set_new_dim_max_free(&$width,&$height){
		$this->set_ndim_max($width,true);
		return true;	
	}
	function set_new_dim_max_fix(&$width,&$height){
		$this->set_ndim_max($width,true);
		$this->set_ndim_fix($height,false);
		return true;	
	}
	function set_new_dim_max_min(&$width,&$height){
		$this->set_ndim_max($width,true);
		$this->set_ndim_min($height,false);
		return true;	
	}
	function set_new_dim_max_max(&$width,&$height){
		$this->set_ndim_max($width,true);
		$this->set_ndim_max($height,false);
		return true;	
	}
	function set_ndim_fix(&$dim,$is_width=true){
		if($cfg=$this->get_dim($is_width)){
			$dim=	$cfg;
		}
		
	}
	function set_ndim_max(&$dim,$is_width=true){
		if($cfg=$this->get_dim($is_width)){
			if($dim>$cfg){
				$dim=	$cfg;
			}
		}
	}
	function set_ndim_min(&$dim,$is_width=true){
		if($cfg=$this->get_dim($is_width)){
			if($dim<$cfg){
				$dim=	$cfg;
			}
		}
	}
	function get_rel_dim($orig,$new,$rel){
		if(!$orig){
			return $rel;	
		}
		if(!$rel){
			return $rel;	
		}
		if(!$new){
			return $rel;	
		}
		return round($new*$rel/$orig);
		
	}
	
	
	function get_new_dim($width,$height){
		if(!$width=$width+0){
			return false;	
		}
		if(!$height=$height+0){
			return false;	
		}
		if($width<=0){
			return false;	
		}
		if($height<=0){
			return false;	
		}
		if(!$code=$this->get_mode_full_code()){
			return false;
		}
		$new_width=$width;
		$new_height=$height;
		$fnc="set_new_dim_".$code;
		
		if(method_exists($this,$fnc)){
			$this->$fnc($new_width,$new_height);	
		}
		$r=array(
			"width"=>$new_width,
			"height"=>$new_height,
		);
		
		return $r;
		
			
	}
	function upload_file($inputname,$newfilename=false,$replace=true,$urlsercurefilename=true){
		$deleteifexists=$this->current_filename;
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		$isimg=true;
		if(!$nf=$fm->upload_file($inputname,$path,$deleteifexists,$isimg,$newfilename,$replace,$urlsercurefilename)){
			return false;	
		}
		return $this->after_upload($nf,$path);
		
	}
	function check_process_upload_input_invalid_attemp_quick($input,&$info=array(),&$invaliduploadattempt=false){
		$newfilename=false;
		$replace=true;
		$urlsercurefilename=true;
		$invaliduploadattempt=false;
		$info=array();
		$result=$this->check_process_upload_input_invalid_attemp($input,$newfilename,$replace,$urlsercurefilename,$info,$invaliduploadattempt);
		return $result;
		
	}
	function check_process_upload_input_invalid_attemp($input,$newfilename=false,$replace=true,$urlsercurefilename=true,&$info=array(),&$invaliduploadattempt=false){
		$invaliduploadattempt=false;
		$info=array();
		if($this->check_process_upload_input($input,$newfilename,$replace,$urlsercurefilename,$info,$invaliduploadattempt)){
			return false;
		}
		if($invaliduploadattempt){
			return true;	
		}
		
		
	}
	function check_process_upload_input($input,$newfilename=false,$replace=true,$urlsercurefilename=true,&$info=array(),&$invaliduploadattempt=false){
		$invaliduploadattempt=false;
		$info=array();
		$deleteifexists=$this->current_filename;
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		$result=$fm->check_process_upload_input($input,$path,$deleteifexists,true,$newfilename,$replace,$urlsercurefilename,false,$info,$invaliduploadattempt);
		return $result;
	}
	
	function process_upload_input($input,$newfilename=false,$replace=true,$urlsercurefilename=true){
		$deleteifexists=$this->current_filename;
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		if(!$nf=$fm->process_upload_input($input,$path,$deleteifexists,true,$newfilename,$replace,$urlsercurefilename)){
			return false;	
		}
		if(is_array($input)){
			if($input["cropoptions"]??null){
				$this->set_crop_options_from_input($input["cropoptions"]);	
			}
		}
		return $this->after_upload($nf,$path);
	}
	function process_upload_input_delete($input){
		
		if(!$filename=$this->current_filename){
			return false;	
		}
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		
		return $fm->process_upload_input_delete($input,$filename,$path);
		
	}
	function get_filemanager(){
		return $this->mainman->get_filemanager();	
	}
	
	function set_img_path($path){
		$this->img_path=$path;	
	}
	function delete_all_files(){
		if(!$path=$this->img_path){
			return false;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		return $fm->delete_path($path);
		

		//echo $this->img_path."<br>";
	}
	function set_current_filename($fn){
		
		$this->current_filename=false;
		if($fn){
			$this->current_filename=basename($fn);
			return true;
		}
		
	}

	
	final function set_proccess_info($info){
		unset($this->_proccess_info);
		if(is_array($info)){
			$this->_proccess_info=$info;
			return true;	
		}
	}
	final function get_proccess_info(){
		return	$this->_proccess_info;
	}
	
	final function unset_proccess_info(){
		unset($this->_proccess_info);	
	}
	
	function process_cfg($cfg=array()){
		$this->set_cfg($cfg);	
	}
	function get_dim($width=true){
		if($width){
			$key="width.dim";	
		}else{
			$key="height.dim";	
		}
		$m=mw_array_get_sub_key($this->cfg,$key)+0;
		if($m<=0){
			return 0;	
		}
		return $m;
		
	
	}
	function get_mode_full_code(){
		$r=$this->get_mode_code(true);	
		$r.="_";	
		$r.=$this->get_mode_code(false);
		return $r;	
	}

	function get_mode_code($width=false){
		if(!$m=$this->get_mode($width)+0){
			$m=0;	
		}
		if($m==1){
			return "fix";	
		}
		if($m==2){
			return "rel";	
		}
		if($m==3){
			return "min";	
		}
		if($m==4){
			return "max";	
		}
		return "free";	
	}
	function get_mode($width=true){
		//modos
		//0 libre
		//1 fijo
		//2 relativo
		//3 mínimo
		//4 max
		if($width){
			$key="width.mode";	
		}else{
			$key="height.mode";	
		}
		$m=mw_array_get_sub_key($this->cfg,$key)+0;
		if($m<0){
			return 0;	
		}
		if($m>4){
			return 0;	
		}
		return $m;
		
	
	}
	function get_full_file_path(){
		if(!$path=$this->img_path){
			return false;	
		}
		if($this->current_filename){
			return $path."/".$this->current_filename;	
		}
	
	}
	
	function set_cfg($cfg=array()){
		$this->cfg=$cfg;
	}
	final function init($mainman,$cod=false){
		$this->mainman=$mainman;
		$this->set_mainap();
		if($cod){
			$this->cod=$cod;	
		}
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	final function __get_priv_mainman(){
		return $this->mainman;	
	}
	
}
?>