<?php

//FPDFhttp://www.fpdf.org/

class mwmod_mw_demo_ui_extmods_fpdf extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("FPDF");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$url=$this->get_exec_cmd_dl_url("hello",array(),"hello.pdf");
		echo "<div><a href='$url' target='_blank'>Hello!</a></div>";
	

	}
	function execfrommain_getcmd_dl_hello($params=array(),$filename=false){
		if(!$this->is_allowed()){
			
			echo "Not allowed";
			return false;
		}
		$pdf=new mwmod_mw_extmods_fpdf();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(40,10,'Hello World!');
		$pdf->Output();

		



	}

	
}
?>