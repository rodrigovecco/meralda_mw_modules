<?php
class  mwmod_mw_lng_msg_lngdefman extends mwmod_mw_lng_msg_lngmanabs{
	private $rewrite_needed;
	function __construct($man){
		$this->init("def",$man);	
	}
	
	function re_write_msgs_if_needed(){
		if(!$this->rewrite_needed){
			return false;	
		}
		
		if(!$p=$this->get_path()){
			return false;	
		}
		if(!$f=$this->get_file_name()){
			return false;	
		}
		if(!$fm=$this->mainap->get_submanager("fileman")){
			return false;	
		}
		if(!$fm->check_and_create_path($p)){
			return false;	
		}
		if(!$fh = fopen($p."/".$f, "w")){
			return false;		
		}
		if($items=$this->get_items()){
			foreach($items as $item){
				 fputs ($fh, $item->get_txt_file_line()."\n");	
			}
		}
		fclose ($fh);
			
	}
	
	function get_or_create_item($cod,$def=false){
		if($item=$this->get_item($cod)){
			return $item;
		}
		if(!$def){
			return false;	
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		$this->init_items();
		$this->rewrite_needed=true;
		$item= $this->create_item($cod);
		$item->set_msg($def);
		$this->add_item($item);
		return $item;
		
	}

}
?>