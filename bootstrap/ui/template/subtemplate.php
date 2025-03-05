<?php
class  mwmod_mw_bootstrap_ui_template_subtemplate extends mwmod_mw_ui_sub_uitemplate{
	
	function __construct($ui){
		$this->set_mainap();
		$this->set_ui($ui);
		//$this->htmlclasspref="sys_inteface_sub";
	}
	function new_tbl_template(){
		$tbl= new mwmod_mw_bootstrap_ui_template_tbl();
		return $tbl;	
	}

	

	
	function exec_sub_interface_mnu(){
		//no usado maintemplate->exec_page_body_sub_interface_bootstrap
		
		if(!$mnu=$this->ui->get_mnu()){
			return false;	
		}
		if(!$mnu->get_allowed_items_num()){
			return false;	
		}
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col(12);
		echo $col->get_html_open_full();
		
		//echo $mnu->get_html();
		
		echo "<nav class='navbar navbar-default' role='navigation'>\n";
		echo '<div class="container-fluid">';
		//echo '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">';
		echo '<div class="collapse navbar-collapse" >';
		echo '<ul class="nav navbar-nav">';
		echo $mnu->get_html_as_list_inner();
		echo '</ul>';
		echo '</div>';
		echo '</div>';
		echo '</nav>';
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		
		
	
	}

	function exec_page_full_body_sub_interface(){
		//no usado maintemplate->exec_page_body_sub_interface_bootstrap
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col();
		echo $col->get_html_open_full();
		echo "<div class='mw-subinterface-header'>\n";
		if($html=$this->ui->get_html_parents_route()){
			echo "<div class='mw-subinterface-header-route' >\n";
			echo $html;
			echo "</div>\n";
				
		}
		echo $this->ui->get_title_for_box();
		//echo $this->ui->get_title_for_box_html();
		echo "</div>\n";
		
		//get_html_parents_route
		
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		$this->exec_sub_interface_mnu();
		
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col();
		echo $col->get_html_open_full()."";
		$gencontclose="";
		if(!$this->ui->omitUIGeneralContainer()){
			echo "<div class='card'>\n";
			$gencontclose="</div>";
		}
		$this->ui->do_exec_on_template($this);
		echo $gencontclose;
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		//$t=$this->new_frm_template();
		//echo get_class($t);

		
		
	}

	
	
}
?>