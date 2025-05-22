<?php
class mwmod_mw_bootstrap_html_grid_row extends mwmod_mw_bootstrap_html_elem{
	function __construct($cont=false){
		$tagname="div";
		$atts=false;
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
		$this->addClass("row");
		//$this->set_att("class","row");
	}
	/*
	* Adds a new column to the row
	* @param int $width The width of the column (1-12)
	* @param mixed $cont The content of the column (optional)
	* @return mwmod_mw_bootstrap_html_grid_col The new column
	*/	
	function add_col($width=12,$cont=false){
		$col= new mwmod_mw_bootstrap_html_grid_col($width,$cont);
		$this->add_cont($col);
		return $col;
	}
	
}

?>