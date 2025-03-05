<?php
class mwmod_mw_bootstrap_html_elem extends mwmod_mw_html_elem{
	function __construct($tagname="div",$atts=false,$cont=false){
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
	}
	
	
}

?>