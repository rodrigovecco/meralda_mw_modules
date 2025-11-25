<?php
class mwmod_mw_demo_ui_ui_htmleditor_imgfm extends mwmod_mw_demo_ui_ui_htmleditor_filemanager{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("selectImage","Seleccionar imagen"));
		$this->imageOnlyMode=true;

		
	}
	

}