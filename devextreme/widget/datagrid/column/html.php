<?php
class mwmod_mw_devextreme_widget_datagrid_column_html extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
		$this->setHtmlMode();
	}
}
?>