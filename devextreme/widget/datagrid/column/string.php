<?php
class mwmod_mw_devextreme_widget_datagrid_column_string extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
	}
	function set_dataoptim_field($field){
		$field->text_mode();	
	}
	function setLongTextMode(){
		$this->mw_js_colum_class="mw_devextreme_datagrid_column_txtLongText";
	}
	function setColorMode(){
		$editTemplate = new mwmod_mw_jsobj_functionext();
		$editTemplate->add_fnc_arg("container");
		$editTemplate->add_fnc_arg("options");
		$editTemplate->add_cont("console.log('editCellTemplate arguments:', container, options);");
		$editTemplate->add_cont("var colorInput = document.createElement('input');");
		$editTemplate->add_cont("colorInput.type = 'color';");
		$editTemplate->add_cont("colorInput.value = options.value || '#ffffff';");
		$editTemplate->add_cont("colorInput.style.width = '100%';");
		$editTemplate->add_cont("colorInput.style.height = '35px';");
		$editTemplate->add_cont("colorInput.style.border = 'none';");
		$editTemplate->add_cont("colorInput.style.cursor = 'pointer';");
		$editTemplate->add_cont("colorInput.addEventListener('change', function() {");
		$editTemplate->add_cont("    options.setValue(this.value);");
		$editTemplate->add_cont("});");
		$editTemplate->add_cont("container.append(colorInput);");
		$this->js_data->set_prop("editCellTemplate", $editTemplate);
		
		// Display template for color preview
		$cellTemplate = new mwmod_mw_jsobj_functionext();
		$cellTemplate->add_fnc_arg("container");
		$cellTemplate->add_fnc_arg("options");
		$cellTemplate->add_cont("console.log('cellTemplate arguments:', container, options);");
		$cellTemplate->add_cont("var colorDiv = document.createElement('div');");
		$cellTemplate->add_cont("colorDiv.style.width = '30px';");
		$cellTemplate->add_cont("colorDiv.style.height = '20px';");
		$cellTemplate->add_cont("colorDiv.style.backgroundColor = options.value || '#ffffff';");
		$cellTemplate->add_cont("colorDiv.style.border = '1px solid #000000';");
		$cellTemplate->add_cont("colorDiv.style.borderRadius = '3px';");
		$cellTemplate->add_cont("colorDiv.style.margin = '0 auto';");
		$cellTemplate->add_cont("colorDiv.title = options.value || '#ffffff';");
		$cellTemplate->add_cont("container.append(colorDiv);");
		$this->js_data->set_prop("cellTemplate", $cellTemplate);
	}
	
	
	
}
?>