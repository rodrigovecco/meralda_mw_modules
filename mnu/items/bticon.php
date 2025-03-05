<?php
class mwmod_mw_mnu_items_bticon extends mwmod_mw_mnu_mnuitem{
	function __construct($cod,$etq,$url=false,$icon="asterisk",$parent){
		$this->init($cod,$etq,$parent,$url);
		$this->bt_icon=$icon;
		//$this->set_param("class","dropdown-menu");
	}
	
	function get_html_open(){
		return "";	
		
	}
	function get_html_close(){
		return "";	
	}
	function get_html(){
		$r.=$this->get_html_open();
		$r.=$this->get_alink();
		$r.=$this->get_html_children();
		$r.=$this->get_html_close();
		return $r;
			
	}
	function get_alink(){
		
		$r.="<a href='".$this->get_url()."' ";
		if($str=$this->get_target()){
			$r.=" target='$str' ";	
		}
		if($str=$this->get_etq()){
			$str=addslashes($str);
			$r.=" title='$str' ";		
		}
		if($str=$this->get_onclick()){
			$q="'";
			if(strpos($str,"'")!==false){
				$q="\"";	
			}
			
			
			$r.=" onclick=$q$str$q ";	
		}
		//class="glyphicon glyphicon-search" aria-hidden="true">
		$r.=" class='glyphicon glyphicon-".$this->bt_icon."' aria-hidden='true' ";
		
		$r.=">";
		//$r.=$this->get_a_inner_html();
		
		$r.="</a>";
				

		return 	$r;
	}
	
}

?>