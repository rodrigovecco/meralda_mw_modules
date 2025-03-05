<?php
class mwmod_mw_helper_captcha_item extends mw_apsubbaseobj{
	var $cod;
	var $man;
	var $height=30;
	var $width=60;
	var $len=4;
	var $secret;
	var $imgelemId;
	var $filename="c";
	public $posy1=3;
	public $posy2=7;
	function __construct($cod,$man){
		$this->cod=$cod;
		$this->man=$man;
		
		
	}
	function validate($input,$clear=true,&$msg=false){
		$r=$this->man->validate($input,$this->cod,$msg);
		if($clear){
			$this->secret=$this->new_secret();
			$this->man->set_item_secret($this);	
		}
		return $r;
	}
	function setPreventCache(){
		$pw=chr(rand(97,122));
		$pw.=chr(rand(97,122));
		
		$this->filename=$pw.time()."";	
	}
	function set_sess_data(){
		$this->man->set_session_data_item($this);	
	}
	function get_img_html(){
		$url=$this->get_url();
		$s="";
		if($i=$this->imgelemId){
			$s.=" id='$i' ";	
		}
		$r="<img src='$url' $s>";
		return $r;	
	}
	/*
	function get_url_for_output($resetSecret=true){
		
	}
	*/
	function get_url(){
		$this->set_sess_data();
		return $this->man->get_exec_cmd_url("img",array("c"=>$this->cod),$this->filename.".png");	
	}
	function prepare_output(){
		$this->secret=$this->new_secret();
		$this->man->set_item_secret($this);	
	}
	function get_data(){
		return $this->man->get_item_data($this);	
	}
	function output(){
		$this->prepare_output();
		$val_img = imagecreate($this->width, $this->height);
		$white = imagecolorallocate($val_img, 255, 255, 255);
		$black = imagecolorallocate($val_img, 0, 0, 0);
		$val_string = $this->secret;
		imagefill($val_img, 0, 0, $black);
		$posx=5;
		$posy=$this->posy1;
		for ($x=0;$x<strlen($val_string);$x++){
			imagestring($val_img, 4,$posx, $posy, $val_string[$x], $white);
			$posx=$posx+10;
			if ($posy==$this->posy1){
				$posy=$this->posy2;
			}else{
				$posy=$this->posy1;
			}
		}
		ob_end_clean();
		header ('Content-type: image/jpeg');
		
		imagepng($val_img);
		imagedestroy($val_img);
	}
	function new_secret(){
		return randPass($this->len);	
	}
	function set_data_from_session(&$data){
		if(!is_array($data)){
			$data=array();	
		}
		$v=round($data["height"]+0);
		if($v<=0){
			$v=$this->height;	
		}
		$this->height=$v;
		$data["height"]=$this->height;

		$v=round($data["width"]+0);
		if($v<=0){
			$v=$this->width;	
		}
		$this->width=$v;
		$data["width"]=$this->width;

		$v=round($data["len"]+0);
		if($v<=0){
			$v=$this->len;	
		}
		$this->len=$v;
		$data["len"]=$this->len;

		$v=round($data["posy1"]+0);
		if($v<=0){
			$v=$this->posy1;	
		}
		$this->posy1=$v;
		$data["posy1"]=$this->posy1;
		
		$v=round($data["posy2"]+0);
		if($v<=0){
			$v=$this->posy2;	
		}
		$this->posy2=$v;
		$data["posy2"]=$this->posy2;
		
	}
	
	
	
}
?>