<?php

//https://github.com/PHPOffice/PhpSpreadsheet/

class mwmod_mw_demo_ui_extmods_phpspreadsheet extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("PhpSpreadsheet");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$helper=new mwmod_mw_extmods_phpspreadsheet();
		if(!$helper->isInstalled()){
			echo "<div>".$this->lng_get_msg_txt("notInstalled","Módulo no instalado").".</div>";
			return;
		}

		$url=$this->get_exec_cmd_dl_url("hello",array(),"hello.xlsx");
		echo "<div><a href='$url' target='_blank'>Hello!</a></div>";
	

	}
	function execfrommain_getcmd_dl_hello($params=array(),$filename=false){
		if(!$this->is_allowed()){
			
			echo "Not allowed";
			return false;
		}
		$helper=new mwmod_mw_extmods_phpspreadsheet();
		
		if(!$helper->registerAutoLoader()){
			echo "Module not installed";
			return false;
		}




		
		//use PhpOffice\PhpSpreadsheet\IOFactory;
		//use PhpOffice\PhpSpreadsheet\Spreadsheet;


		$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$spreadsheet->getProperties()->setCreator('Meralda')
		    ->setLastModifiedBy('Meralda')
		    ->setTitle('Office 2007 XLSX Test Document')
		    ->setSubject('Office 2007 XLSX Test Document')
		    ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
		    ->setKeywords('office 2007 openxml php')
		    ->setCategory('Test result file');
		// Add some data
		$spreadsheet->setActiveSheetIndex(0)
		    ->setCellValue('A1', 'Hello')
		    ->setCellValue('B2', 'world!')
		    ->setCellValue('C1', 'Hello')
		    ->setCellValue('D2', 'world!');

		// Miscellaneous glyphs, UTF-8
		$spreadsheet->setActiveSheetIndex(0)
		    ->setCellValue('A4', 'Miscellaneous glyphs')
		    ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

		// Rename worksheet
		$spreadsheet->getActiveSheet()->setTitle('Simple');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$spreadsheet->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="01simple.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');

		



	}

	
}
?>