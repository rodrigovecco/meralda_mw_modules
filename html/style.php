<?php
class mwmod_mw_html_style extends mwmod_mw_html_atts implements mwmod_mw_html_att_interface{
	function __construct($atts=false){
		$this->set_props_init($atts);
		
	}
	
	public function setValue($value){
		$this->set_style_atts($value,true);
		return true;	
	}
	public function getValue(){
		return $this->get_atts_for_tag();	
	}

	
	function get_style_att(){
		return false;	
	}
	function add_style_atts($atts=false){
		return $this->set_style_atts($atts,false);
	}
	function set_style_atts($atts=false,$overwrite=true){
		if($overwrite){
			$new=array();	
			$this->set_props($new);
		}
		if(!$atts){
			return false;	
		}
		if(is_array($atts)){
			$r=$atts;	
		}else{
			$r=$this->get_atts_from_string($atts);	
		}
		
		if(!$r){
			return false;
		}
		if(!is_array($r)){
			return false;
		}
		foreach ($r as $key=>$val){
			$this->set_att($key,$val);
		}
		return true;
	}
	function get_atts_from_string($atts=false){
		$r=array();
		if(!is_string($atts)){
			return $r;	
		}
		$a=explode(";",$atts);
		foreach($a as $s){
			if(is_string($s)){
				$b=explode(":",$s,2);
				if($b[0]){
					if((is_string($b[1]))or(is_numeric($b[1]))){
						$val=trim($b[1]);
						if($cod=trim($b[0])){
							if((!$r[$cod])or($val)){
								$r[$cod]=$val;	
							}
						}
					}
				}
			}
		}
		return $r;
	
	}
	function get_atts_for_tag(){
		if(!$list=$this->get_atts_for_tag_as_str_array()){
			return "";	
		}
		return implode("; ",$list);
	}

	function get_att_str_for_tag($cod,$att){
		if(is_object($att)){
			return false;
		}
		if(is_bool($att)){
			if($att){
				return "$cod:true";	
			}else{
				return "$cod:false";	
			}
		}
		if(!is_string($att)){
			if(!is_numeric($att)){
				return false;	
			}
		}else{
			if(!$att){
				return false;
			}
		}
		return $cod.":".$att;
		
	}
	function get_att_for_tag($cod,$att){
		if(is_object($att)){
			if(!$att){
				return false;	
			}
			
			if(method_exists($att,"get_as_style_att")){
				return $att->get_as_style_att($cod);	
			}elseif(method_exists($att,"__toString")){
				return $this->get_att_str_for_tag($cod,$att->__toString());
			}
		}else{
			return $this->get_att_str_for_tag($cod,$att);	
		}
	}

	function __toString(){
		
		return $this->get_atts_for_tag();	
	}
	function set_props_init($atts=false){
		if(is_string($atts)){
			$atts=$this->get_atts_from_string($atts);	
		}
		
		
		if(!is_array($atts)){
			$atts=array();	
		}
		$this->set_props($atts);
	}
}
?>