<?php
class  mwmod_mw_lng_lngman extends mw_apsubbaseobj{
	
	private $currentindex=0;
	private $current_lng_man;
	
	private $lngs=array();
	private $lngs_by_index=array();
	private $_msgs_man=array();
	
	private $_mail_msgs_man;
	
	function __construct($ap){
		$this->init($ap);	
	}
	final function get_mail_msgs_man(){
		if(!$this->_mail_msgs_man){
			$this->_mail_msgs_man=new mwmod_mw_lng_mailmsg_man($this);
		}
		return $this->_mail_msgs_man;
	}
	function re_write_def_msgs_mans_if_needed(){
		if(!$items=$this->get_msgs_mans()){
			return false;	
		}
		foreach($items as $cod=>$item){
			$item->re_write_def_msgs_mans_if_needed();	
		}
	}
	final function get_msgs_mans(){
		return $this->_msgs_man;	
	}
	
	
	final function get_msgs_man($cod=false){
		if(!$cod){
			return false;	
		}
		if(isset($this->_msgs_man[$cod])){
			if($this->_msgs_man[$cod]){
				return 	$this->_msgs_man[$cod];
			}
		}
		
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		$this->_msgs_man[$cod]= new mwmod_mw_lng_msg_man($cod,$this);
		return 	$this->_msgs_man[$cod];
		
	}
	
	final function get_current_lng_man(){
		if(!isset($this->current_lng_man)){
			if($lng=$this->get_lng_by_index($this->currentindex)){
				$this->current_lng_man=$lng;		
			}
		}
		if(isset($this->current_lng_man)){
			return 	$this->current_lng_man;
		}
	}
	final function set_current_lng($index){
		unset($this->current_lng_man);
		$this->currentindex=$index+0;	
	}
	function set_lng_by_code($code){
		if(!$lng=$this->get_lng($code)){
			return false;
		}
		return $this->set_current_lng($lng->index);
	}
	function register_msgs(){
		return false;	
	}
	function do_register_msg($code,$msgslist,$objsrc=false){
		//podrá usatse para almacenar los mensajes por defecto en el futuro	
	}
	function format_msg($msg,$params=false){
		if(is_array($params)){
			foreach($params as $cod=>$v){
				$msg=str_replace("%$cod%",$v,$msg);
			}
		}
		return $msg;
	}
	function get_stored_msg($code,$index=0){
		if($lng=$this->get_lng_by_index($index)){
			return $lng->get_stored_msg($code);	
		}
	}
	function get_msg_by_list($msgslist,$objsrc=false,$code=false){
		if(!is_array($msgslist)){
			return $msgslist;	
		}
		if(!sizeof($msgslist)){
			return $code;	
		}
		$last=sizeof($msgslist)-1;
		if(is_array($msgslist[$last])){
			$params=array_pop($msgslist);	
		}else{
			$params=null;
		}
		if($this->check_msg_code($code)){
			if($this->register_msgs()){
				$this->do_register_msg($msgslist,$code,$objsrc);	
			}
			if($msg=$this->get_stored_msg($code,$this->get_current_lng_index())){
				return $this->format_msg($msg,$params);	
			}
			
		}
		if($msg=$this->get_msg_from_list($msgslist,$objsrc)){
			return $this->format_msg($msg,$params);	
		}
		if($msg=$this->get_stored_msg($code)){
			return $this->format_msg($msg,$params);	
		}
	
		
	}
	function get_msg_from_list($msgslist,$objsrc=false){
		if($msg=$this->get_msg_from_list_by_index($msgslist,$this->get_current_lng_index(),$objsrc)){
			return $msg;	
		}
		if($msg=$this->get_msg_from_list_by_index($msgslist,0,$objsrc)){
			return $msg;	
		}
	}
	function get_msg_from_list_by_index($msgslist,$index=0,$objsrc=false){
		$custumindex=$this->get_current_lng_index_for_objsrc($index,$objsrc);
		if(is_numeric($custumindex)){
			return $msgslist[$custumindex];
				
		}
		
		
	}
	function get_current_lng_index(){
		return $this->currentindex; 		
	}
	function get_lng_code($index){
		
		if($lng=$this->get_lng_by_index($index)){

			return $lng->code;	
		}
	}
	function get_current_lng_index_for_objsrc($index=0,$objsrc=false){
		if(!$objsrc){
			return $index;	
		}
		if($code=$this->get_lng_code($index)){
		
			$r=$objsrc->_get_lng_index_for_code($code);
			if(is_numeric($r)){
				return $r;		
			}
			
		}
		return $index;		
	}
	function get_msg_by_list_and_code($msgslist,$objsrc=false){
		if(!is_array($msgslist)){
			return $msgslist;	
		}
		$code=array_shift($msgslist);
		return $this->get_msg_by_list($msgslist,$objsrc,$code);
	}
	final function get_lng($cod){
		if(!$this->check_str_key($cod)){
			return false;
		}
		return $this->lngs[$cod];	
	}
	final function get_lng_by_index($index){
		if(!is_numeric($index)){
			return false;
		}
		return $this->lngs_by_index[$index];	
	}
	final function get_lngs(){
		return $this->lngs;	
	}

	function create_lng_item($cod,$name=false){
		$item= new mwmod_mw_lng_lngitem($cod,$this,$name);
		return $item;	
	}
	final function add_lng_by_code($cod,$name=false){
		if(!$this->check_str_key($cod)){
			return false;
		}
		if(!$cod=basename($cod)){
			return false;	
		}
		if($item=$this->create_lng_item($cod,$name)){
			return $this->add_lng($item);	
		}
	}

	final function add_lng($lngitem){
		$cod=$lngitem->get_code();	
		if($this->lngs[$cod]??false){
			return false;	
		}
		$index=sizeof($this->lngs);
		$lngitem->set_index($index);
		$this->lngs[$cod]=$lngitem;
		$this->lngs_by_index[]=$lngitem;
		return $this->lngs[$cod];
		
	}
	function get_locale_cod(){
		if(!$man=$this->get_current_lng_man()){
			return false;	
		}
		return $man->get_locale_cod();
	}
	
	function check_msg_code($code){
		if(!$this->check_str_key($code)){
			return false;
		}
		if(strpos($code," ")===false){
			return true;	
		}
	}
	final function __get_priv_currentindex(){
		return $this->currentindex; 	
	}

	final function init($ap){
		$this->set_mainap($ap);	
	}

}
//include_once "item.php";
?>