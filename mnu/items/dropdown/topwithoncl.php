<?php
class mwmod_mw_mnu_items_dropdown_topwithoncl extends mwmod_mw_mnu_items_dropdown_top{
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
	}
	//no usado
	function get_alink(){
		
		$r.="<a href='".$this->get_url()."' ";
		if($str=$this->get_target()){
			$r.="target='$str' ";	
		}
		if($str=$this->get_onclick()){
			$q="'";
			if(strpos($str,"'")!==false){
				$q="\"";	
			}
			
			
			$r.="onclick=$q$str$q ";	
		}
		
		
		
		$r.=">";
		$r.=$this->get_a_inner_html();
		$r.="</a>";
		
		$r.="<a href='#' ";
		$r.=" class='dropdown-toggle' data-toggle='dropdown' ";
		$r.=">";
		
		$r.=" <i class='fa fa-caret-down'></i>";
		$r.="</a>";
				

		return 	$r;
	}
	
}

?>