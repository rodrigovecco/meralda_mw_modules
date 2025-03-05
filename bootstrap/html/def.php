<?php
class mwmod_mw_bootstrap_html_def extends mwmod_mw_bootstrap_html_elem{
	function __construct($class=false,$tagname="div",$cont=false){
		$this->set_tagname($tagname);
		$this->init_atts(false);
		if($cont!==false){
			$this->add_cont($cont);
		}
		if($class){
			$this->set_att_class($class);	
		}
	}
	function set_att_class($val){
		$this->set_att("class",$val);	
	}

	
}

?>