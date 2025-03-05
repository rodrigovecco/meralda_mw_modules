<?php
class mwmod_mw_ui_debug_mainuitest extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Main UI");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$pm=$this->mainap->get_sub_path_man("debug","instance");
		$testfile="mainuitest.php";
		$filepath=$pm->get_full_path_filename($testfile);
		echo "<p>Ejecutando código de prueba en file:<br>$filepath</p>";
		if($realfile=$pm->file_exists($testfile)){
			include $realfile;
		}



		//$util=new mwmod_mw_util_text();
		//echo $util->time2id();


		/*
		$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789";
		$base = strlen($letters); 

		// Get the current Unix timestamp
		$timestampOrig = time()-strtotime("2020-10-01");
		//$timestampOrig=date("ymdHis",$timestampOrig);
		$timestamp=$timestampOrig;


		
		$baseNString = "";

		while ($timestamp > 0) {
		    // Calculate the remainder when dividing by the base
		    $remainder = $timestamp % $base;

		    // Prepend the corresponding character from the letters
		    $baseNString = $letters[$remainder] . $baseNString;

		    // Update the timestamp by integer division
		    $timestamp = (int)($timestamp / $base);
		}

		echo "<div>$timestampOrig Base-$base representation: " . $baseNString."</div>";
		*/
		

		//$file=__FILE__;
		//echo "<p>Puedes escribir código de prueba acá:<br>$file</p>";
		
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>