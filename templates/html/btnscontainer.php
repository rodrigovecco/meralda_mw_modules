<?php

class mwmod_mw_templates_html_btnscontainer extends mwmod_mw_html_elem {
	function __construct($cont=false,$tagname="div",$atts=false){
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
		$this->set_att("class","mw-subinterface-btns-container");
	}
}

?>