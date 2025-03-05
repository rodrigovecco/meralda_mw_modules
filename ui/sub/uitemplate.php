<?php
class mwmod_mw_ui_sub_uitemplate extends mwmod_mw_templates_basetemplate{
	
	private $ui;
	function __construct($ui){
		$this->set_mainap();
		$this->set_ui($ui);
		$this->htmlclasspref="sys_inteface_sub";
	}
	function new_frm_template(){
		
		$t=new mwmod_mw_templates_frmtemplate($this->htmlclasspref);
		
		return $t;
	}

	function exec_sub_interface_mnu(){
		
		if(!$mnu=$this->ui->get_mnu()){
			return false;	
		}
		if(!$mnu->get_allowed_items_num()){
			return false;	
		}
		echo $this->get_html_tag_open("mnu").$mnu->get_html()."</div>";
	
	}
	
	function exec_page_full_body_sub_interface(){
		$box=$this->new_box();
		echo  $box->get_html_open_full();
		echo $box->get_html_open_title();
		echo $box->get_html_open_title_in();
		echo $this->ui->get_title_for_box_html();
		echo $box->get_html_close_title_in();
		echo $box->get_html_close_title();
		
		echo $box->get_html_open_box();
		$this->exec_sub_interface_mnu();
		
		//falta menu
		//if($mnu=$this->ui->
		echo "<table><tr><td>";
		$this->ui->do_exec_on_template($this);
		echo "</td></tr></table>";
		echo $box->get_html_close();
		
	}
	function new_box($title=""){
		$t= new mwmod_mw_templates_box($title);
		$t->htmlclasspref=$this->htmlclasspref;
		return $t;
	}
	
	final function set_ui($ui){
		$this->ui=$ui;	
	}
	final function __get_priv_ui(){
		return $this->ui;	
	}
	
	function new_tbl_template(){
		$tbl= new mwmod_mw_templates_tbl();
		return $tbl;	
	}

	/*
	function get_html_sub_open(){
		return $this->get_html_tag_open("sibody");
	}
	function get_html_close_close(){
		return "</div>";	
	}
	function get_html_sub_open_in(){
		return $this->get_html_tag_open("sicont");
		
	}
	function get_html_sub_close_in(){
		return "</div>";	
	}
	function get_html_sub_close(){
		return "</div>";	
	}
	function get_html_close_close_in(){
		return "</div>";	
	}
	
	function get_html_tag($cont=false,$classcode=false,$tagname="div"){
		if($cont===false){
			return false;
		}
		$html=$this->get_html_tag_open($classcode,$tagname).$cont."</".$tagname.">";
		return $html;
	
	}
	function get_html_msgbox($cont=false){
		return $this->get_html_tag($cont,"msgbox");
	}

	function get_html_title($cont=false){
		return $this->get_html_tag($cont,"title");
	}
	
	function get_html_main_open(){
		
		return $this->get_html_tag_open("full");
		
	}
	function get_html_main_close(){
		return "</div>";	
	}
	function get_mnu_item_html($mnu){
		if(!$mnu){
			return false;	
		}
		$html=$this->get_html_tag_open("inlinemnu").$mnu->get_html()."</div>";
		return $html;
	}
	
	
	function get_mnu_html($mnu){
		if(!$mnu){
			return false;	
		}
		$html=$this->get_html_tag_open("mnu").$mnu->get_html()."</div>";
		return $html;
	}
	*/
	
	
	function __call($a,$b){
		return false;	
	}
	
}
?>