<?php
//rvh 2015-01-10 v 1
class mwmod_mw_bootstrap_helper_jsie extends mwmod_mw_html_manager_item_js{
	function __construct($cod="btjsie"){
		$this->init_item($cod);
		
	}
	function get_html_declaration(){
		$html="<!--[if lt IE 9]>\n";
		$html.="\t<script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>\n";
		$html.="\t<script src='https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js'></script>\n";
		$html.="<![endif]-->\n";
		return $html;
	}

	
}
?>