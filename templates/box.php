<?php
//
class mwmod_mw_templates_box extends mwmod_mw_templates_basetemplate{
	public $title;
	function __construct($title=""){
		$this->title=$title;
	}
	function get_html_open_mnu(){
		$html="<div>";
		return $html;
		
	}
	function get_html_close_mnu(){
		$html.="</div>";
		return $html;
			
	}
	function get_html_open_full(){
		$html.= $this->get_html_tag_open("box");
		return $html;
		
	}
	function get_html_open_title(){
		$html.= $this->get_html_tag_open("lableout");
		$html.= $this->get_html_tag_open("lablein");
		
		
		return $html;
		
	}
	function get_html_open_title_in(){
		$html.= $this->get_html_tag_open("top");
		return $html;
		
	}
	function get_html_close_title(){
		
		
		$html.="</div>";
		$html.= $this->get_html_tag_open("lable_end");
		$html.="</div>";

		$html.="</div>";
		return $html;
		
	}
	function get_html_open_box(){
		$html.= $this->get_html_tag_open("boxin");
		return $html;
		
	}
	function get_html_open(){
		$html.= $this->get_html_open_full();
		$html.= $this->get_html_open_title();
		$html.= $this->get_html_open_title_in();
		$html.=$this->title;
		$html.= $this->get_html_close_title_in();
		$html.= $this->get_html_close_title();
		
		$html.= $this->get_html_open_box();

		return $html;
			
	}
	function get_html_close(){
		$html.=$this->get_html_close_box();
		$html.=$this->get_html_close_full();
		return $html;
			
	}
	function get_html_close_title_in(){
		$html.="</div>";
		return $html;
			
	}
	function get_html_close_box(){
		$html.="</div>";
		return $html;
			
	}
	function get_html_close_full(){
		$html.="</div>";
		return $html;
			
	}
	

}


?>