<?php

class mwmod_mw_html_elem extends mwmod_mw_html_abselem implements mwmod_mw_html_cont_interface{
	private $dont_close=false;
	//private $atts;
	private $tagname;
	private $cont=array();
	private $_keycontlist=array();
	
	private $visible=true;
	var $only_visible_when_has_cont=false;
	
	/**
  * @return integer Indicates the number of items.
  */
	
	function __construct($tagname=false,$atts=false,$cont=false){
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
	}
	function addLineBreak(){
		$e=new mwmod_mw_html_lb();
		return $this->add_cont($e);	
	}
	function setEventAtt($cod="onclick",$att=false){
		if(!$att){
			$att=new mwmod_mw_html_att_js();
		}
		$this->atts->set_att($cod,$att);
		return $att;
	}

	
	function setAtts($list){//id='xx' name='qqq' src="qqq"
		$this->atts->setAtts($list);	
	}
	function remove_class($class){
		if(!$class){
			return;	
		}
		if(!$list=$this->get_class_list()){
			return false;	
		}

		
		if(!isset($list[$class])){
			return false;
		}
		if(!$list[$class]){
			return false;	
		}
		unset($list[$class]);
		
		$this->set_att("class",implode(" ",$list));
		return true;
		
	}
	function addClass($class){
		if(!$class){
			return;	
		}
		$cl=explode(" ",$class);
		foreach($cl as $c){
			$this->add_class($c);	
		}
	}
	function add_class($class){
		if(!$class){
			return;	
		}
		if(!$list=$this->get_class_list()){
			if($att=$this->get_att("class")){
				if(is_object($att)){
					return false;	
				}
			}
			$this->set_att("class",$class);
			return true;	
		}
		if(isset($list[$class])and $list[$class]){
			return true;	
		}
		$list[]=$class;
		$this->set_att("class",implode(" ",$list));
		return true;
		
	}
	function get_class_list(){
		$att=$this->get_att("class");
		if(is_string($att)){
			if($list=explode(" ",$att)){
				$r=array();
				foreach($list as $c){
					if($c){
						$r[$c]=$c;	
					}
				}
				return $r;
			}
			//return 		
		}
		
	}
	function get_as_js_val(){
		return "'".mw_text_nl_js($this->get_as_html())."'";		
	}
	function is_visible(){
		if(!$this->visible){
			return false;	
		}
		if(!$this->only_visible_when_has_cont){
			return true;
		}else{
			$cont=$this->__get_priv_cont();
			if(sizeof($cont)){
				foreach($cont as $c){
					if($c){
						if(is_object($c)){
							if(method_exists($c,"is_visible")){
								if($c->is_visible()){
									return true;	
								}
							}
						}else{
							return true;	
						}
					}
				}
					
			}
		}
	}
	final function set_visible($val=true){
		$this->visible=$val;	
	}
	final function unset_key_cont_list(){
		$this->_keycontlist=array();	
	}
	final function get_key_cont_list(){
		return $this->_keycontlist;	
	}
	final function get_key_cont($cod){
		if($cod){
			if($this->_keycontlist[$cod]){
				return $this->_keycontlist[$cod];	
			}
		}
	}
	final function unset_key_cont($cod){
		if($cod){
			if($this->_keycontlist[$cod]){
				$r=$this->_keycontlist[$cod];
				unset($this->_keycontlist[$cod]);
				return $r;	
			}
		}
		
	}
	final function set_key_cont($cod,$cont){
		if($cod){
			if($cont){
				if(is_object($cont)){
					$this->_keycontlist[$cod]=$cont;
					return $cont;
				}
			}
		}
	}
	function get_style($cod=false){
		if(!$att=$this->atts->get_style_att()){
			return false;	
		}
		
		if(!$cod){
			return 	$att;
		}else{
			return $att->get_att($cod);	
		}
	}
	function add_style_atts($atts=false){
		if(!$att=$this->atts->get_style_att()){
			return false;	
		}
		return $att->add_style_atts($atts);	
	}
	function set_style_atts($atts=false,$overwrite=true){
		if(!$att=$this->atts->get_style_att()){
			return false;	
		}
		return $att->set_style_atts($atts,$overwrite);	
			
	}
	
	function set_style($key,$val){
		
		if(!$att=$this->atts->get_style_att()){
			return false;	
		}
		return $att->set_att($key,$val);	
	}
	
	function get_att($cod=false){
		return $this->atts->get_att($cod);	
	}
	function set_att($key,$val){
		return $this->atts->set_att($key,$val);	
	}
	function get_html_in(){
		return $this->get_cont_as_html();	
	}
	function get_html_before_open(){
		return "";	
	}
	function get_html_after_open(){
		return "";	
	}
	function get_html_before_close(){
		return "";	
	}
	function get_html_after_close(){
		return "";	
	}
	function get_html_open(){
		$r="<".$this->tagname;
		if($a=$this->atts->get_atts_for_tag()){
			$r.=" ".$a;	
		}
		$r.=">";
		if($this->nloncont){
			$r.="\n";	
		}
		return $r;
	}
	function get_html_close(){
		if($this->dont_close){
			return "";		
		}
		$r="</".$this->tagname.">";
		if($this->nlonclose){
			$r.="\n";	
		}
		return $r;
		
	}
	function get_html_open_full(){
		$r=$this->get_html_before_open();
		$r.=$this->get_html_open();
		$r.=$this->get_html_after_open();
		return $r;
		
	}
	function get_html_close_full(){
		$r=$this->get_html_before_close();
		$r.=$this->get_html_close();
		$r.=$this->get_html_after_close();
		return $r;
		
	}
	function get_as_html(){
		if(!$this->is_visible()){
			return "";
		}
		$r=$this->get_html_before_open();
		$r.=$this->get_html_open();
		$r.=$this->get_html_after_open();
		$r.=$this->get_html_in();
		$r.=$this->get_html_before_close();
		$r.=$this->get_html_close();
		$r.=$this->get_html_after_close();
		return $r;
	}
	function do_output(){
		if(!$this->is_visible()){
			return false;
		}
		
		echo $this->get_html_before_open();
		echo $this->get_html_open();
		echo $this->get_html_after_open();
		$this->do_output_in();
		echo $this->get_html_before_close();
		echo $this->get_html_close();
		echo $this->get_html_after_close();
			
	}
	function do_output_in(){
		$this->do_output_cont();	
	}
	function do_output_cont_elem($c){
		if(is_object($c)){
			if(method_exists($c,"do_output")){
				$c->do_output();
				return;
			}elseif(method_exists($c,"get_as_html")){
				echo $c->get_as_html();
				return;
			}elseif(method_exists($c,"__toString")){
				echo $c->__toString();
				return;
			}
		}else{
			if(is_array($c)){
				foreach($c as $cc){
					$this->do_output_cont_elem($cc);	
				}
			}else{
				echo $c;	
			}
		}

	}
	function do_output_cont(){
		$cont=$this->__get_priv_cont();
		foreach($cont as $c){
			$this->do_output_cont_elem($c);
		}
			
	}
	final function set_cont($cont=false){
		$this->cont=array();
		if($cont){
			$this->add_cont($cont);	
		}
	}
	
	function add_cont_elem($cont=false,$tag="div",$atts=false){
		$c=new mwmod_mw_html_elem($tag,$atts,$cont);
		$this->add_cont($c);
		return $c;
		
	}
	function addCont($cont=false){
		if($cont){
			if(is_object($cont)){
				$this->add_cont($cont);
				return $cont;
			}
		}
		$c=new mwmod_mw_html_cont_varcont($cont);
		$this->add_cont($c);
		return $c;
		
	}
	/**
	* @param string $cont sdasd
	* @return mwmod_mw_html_elem balbla bla
	*/
	function add_cont_as_html($cont){
		if(!$cont){
			return false;	
		}
		if(is_object($cont)){
			$this->add_cont($cont);
			return $cont;
		}
		$c=new mwmod_mw_html_elem("div",false,$cont);
		$this->add_cont($c);
		return $c;
		
		
	}
	final function add_cont($cont){
		if(!$cont){
			if(!is_numeric($cont)){
				return false;	
			}
		}
		if(is_array($cont)){
			foreach($cont as $c){
				$this->cont[]=$c;	
			}
		}else{
			$this->cont[]=$cont;		
		}
	}
	function get_cont_as_html(){
		$cont=$this->__get_priv_cont();
		$r="";
		foreach($cont as $c){
			if(is_object($c)){
				if(method_exists($c,"get_as_html")){
					$r.=$c->get_as_html();	
				}elseif(method_exists($c,"__toString")){
					$r.=$c->__toString();	
				}
			}else{
				$r.=$c;	
			}
		}
		return $r;
	}
	
	function get_debug_info(){
		$r["this"]=get_class($this);
		$r["tagname"]=	$this->tagname;
		$r["atts"]=	$this->atts;
		$r["attsstr"]=	$this->atts->get_atts_for_tag();
		
		
		return $r;
	}
	/*
	final function init_atts($atts=false){
		if($atts){
			if(is_object($atts)){
				if(is_a($atts,"mwmod_mw_html_atts")){
					$this->atts=$atts;
					
					return $this->atts;
				}
			}
		}
		$this->atts=new mwmod_mw_html_atts($atts);
		return $this->atts;
	}
	*/
	
	function get_def_tag_name(){
		return "div";	
	}
	final function set_dont_close($val=true){
		$this->dont_close=$val;	
	}
	final function set_tagname($tagname=false){
		if(!$tagname){
			$tagname=$this->get_def_tag_name();	
		}
		if(!is_string($tagname)){
			$tagname=$this->get_def_tag_name();	
		}
		$this->tagname=$tagname;
		return $tagname;
		
		
	}
	final function __get_priv_tagname(){
		return $this->tagname;	
	}
	/*
	final function __get_priv_atts(){
		return $this->atts;	
	}
	*/
	final function __get_priv_cont(){
		return $this->cont;	
	}
	final function __get_priv_dont_close(){
		return $this->dont_close;	
	}
	final function __get_priv_visible(){
		return $this->visible;	
	}
	
	
	function __toString(){
		return $this->get_as_html();	
	}
	
}

?>