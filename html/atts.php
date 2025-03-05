<?php
class mwmod_mw_html_atts extends mw_object_as_array{
	function __construct($atts=false){
		$this->set_props_init($atts);
		
	}
	function get_style_att(){
		if($att=$this->get_att("style")){
			if(is_object($att)){
				if(is_a($att,"mwmod_mw_html_style")){
					return $att;	
				}
			}
			
		}
		$style=new mwmod_mw_html_style($att);
		$this->set_att("style",$style);
		return $style;
	}
	
	function get_att($cod=false){
		if(!$cod){
			return false;	
		}
		
		if(!is_string($cod)){
			return false;	
		}
		return $this->get_prop_from_key_dot($cod);	
	}
	function setAtts($list){
		if(!$list){
			return false;	
		}
		
		if(!is_array($list)){
			$a=explode(" ",$list."");
			$list=array();
			foreach($a as $p){
				if($p=trim($p)){
					$b=explode("=",$p,2);
					if(sizeof($b)==2){
						if($k=trim($b[0])){
							$v=trim($b[1]," '\"")."";
							$list[$k]=$v;
						}
					}
				}
			}
		}
		foreach($list as $k=>$v){
			$this->setAtt($k,$v);	
		}
		
	}
	function setAtt($key,$val){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			return false;	
		}
		if(!$att=$this->get_att($key)){
			return $this->set_att($key,$val);	
		}
		if(is_object($att)){
			if($att instanceof mwmod_mw_html_att_interface){
				return $att->setValue($val);
			}
		}
		return $this->set_att($key,$val);
	}
	function set_att($key,$val){
		if(!$key){
			return false;	
		}
		
		if(!is_string($key)){
			return false;	
		}
		return $this->set_prop_from_key_dot($key,$val);	
	}
	

	function get_att_str_for_tag($cod,$att){
		if(is_object($att)){
			return false;
		}
		if(is_bool($att)){
			if($att){
				return "$cod='$cod'";	
			}else{
				return false;	
			}
		}
		if(!is_string($att)){
			if(!is_numeric($att)){
				return false;	
			}
		}else{
			if(!$att){
				if(!is_numeric($att)){
					return false;
				}
			}
		}
		$q="'";
		if(strpos($att,"'")!==false){
			if(strpos($att,"\"")!==false){
				$att=htmlspecialchars($att);
			}else{
				$q="\"";	
			}
		}
		return $cod."=".$q.$att.$q;
		
	}
	function get_att_for_tag($cod,$att){
		if(is_object($att)){
			if(!$att){
				return false;	
			}
			
			if(method_exists($att,"get_as_att")){
				return $att->get_as_att($cod);
			}elseif($att instanceof mwmod_mw_html_att_interface){
				return $this->get_att_str_for_tag($cod,$att->getValue());
			}elseif(method_exists($att,"__toString")){
				
				return $this->get_att_str_for_tag($cod,$att->__toString());
			}
		}else{
			return $this->get_att_str_for_tag($cod,$att);	
		}
	}
	function get_atts_for_tag_as_str_array(){
		if(!$atts=$this->get_props()){
			return false;	
		}
		if(!is_array($atts)){
			return false;	
		}
		$list=array();
		foreach($atts as $cod=>$att){
			if($str=$this->get_att_for_tag($cod,$att)){
				$list[]=$str;
			}
		}
		return $list;
			
	}
	function get_atts_for_tag(){
		if(!$list=$this->get_atts_for_tag_as_str_array()){
			return "";	
		}
		return implode(" ",$list);
	}
	
	function __mw_array_allow_use_this_object(){
		return true;	
	}
	function set_props_init($atts=false){
		if(!is_array($atts)){
			$atts=array();	
		}
		/*
		$style=false;
		$createstyle=true;
		if($atts["style"]){
			
			if(is_object($atts["style"])){
				if(is_a($atts["style"],"mwmod_mw_html_style")){
					$createstyle=false;
				}
			}
			if($createstyle){
				$style=$atts["style"];
			}
			
			
		}
		$atts["style"]=new mwmod_mw_html_style($style);
		*/
		$this->set_props($atts);
	}
}
?>