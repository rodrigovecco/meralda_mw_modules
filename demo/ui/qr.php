<?php

//QR https://nimiq.github.io/qr-scanner

class mwmod_mw_demo_ui_qr extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("QR");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "QR";
		$url=$this->get_exec_cmd_dl_url("qr");
		echo "<div><img src='$url'></div>";

		

		echo "<hr>";
		echo "<video id='qr-scanner-video' width='300' height='300'></video>";
		echo "<p><select class='form-select' id='camera_selector'></select></p><hr>";
	
		echo "<textarea id='qr-scanner-result' class='form-control'></textarea>";

		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$options=new mwmod_mw_jsobj_obj();
		$options->set_prop("returnDetailedScanResult",true);
		$options->set_prop("highlightScanRegion",true);
		//$options->set_prop("calculateScanRegion",true);

		
		$js->add_cont("var videoElem=mw_get_element_by_id('qr-scanner-video');\n");
		$js->add_cont("var camList=mw_get_element_by_id('camera_selector');\n");
		$js->add_cont("function ui_demo_qr_on_scan_result(result){\n");
		$js->add_cont("console.log('decoded qr code:', result);\n");
		$js->add_cont("mw_get_element_by_id('qr-scanner-result').value=result.data;\n");
		$js->add_cont("}\n");

		//$js->add_cont("var scanner = new QrScanner(videoElem,result => console.log('decoded qr code:', result),".$options->get_as_js_val().");\n");
		$js->add_cont("var scanner = new QrScanner(videoElem,result => ui_demo_qr_on_scan_result(result),".$options->get_as_js_val().");\n");
		$js->add_cont("scanner.start().then(() => {
         QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
            const option = document.createElement('option');
            option.value = camera.id;
            option.text = camera.label;
            camList.add(option);
        }));
    });\n");
		$js->add_cont("camList.addEventListener('change', function(e){scanner.setCamera(e.target.value); scanner.start();});");

		//scanner: qrScanner.setCamera(facingModeOrDeviceId);
		echo $js->get_js_script_html();



		

	}
	function execfrommain_getcmd_dl_qr($params=array(),$filename=false){
		if(!$this->is_allowed()){
			
			echo "Not allowed";
			return false;
		}
		//ob_end_clean();
		$qrMan=new mwmod_mw_extmods_qr();
		$txt="¡Hola Mundo!";
		if(!$qr=$qrMan->newQR($txt)){
			echo "error";
			return false;
		}
		$qr->output_image();



	}

	function prepare_before_exec_no_sub_interface(){
		
		$jsman=$this->maininterface->jsmanager;
		
		
		$jsman->add_item_by_cod("/res/js/ui/mwui.js");
		$jsman->add_item_by_cod("/res/qr-scanner/qr-scanner.umd.min.js");
		//$jsman->add_item_by_cod("/res/qr-scanner/qr-scanner-worker.min.js");
		

		
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}
}
?>