<?php
class mwmod_mw_bootstrap_html_grid_container extends mwmod_mw_bootstrap_html_elem{
	function __construct($notfluid=true,$cont=false){
		if($notfluid){
			$fluid=false;	
		}else{
			$fluid=true;	
		}
		
		$tagname="div";
		$atts=false;
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
		if($fluid){
			$this->set_att("class","container-fluid");	
		}else{
			$this->set_att("class","container");
		}
	}
	function addRow(){
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$this->add_cont($row);
		return $row;
	
	}
	
}

?>