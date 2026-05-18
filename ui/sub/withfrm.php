<?php

abstract class mwmod_mw_ui_sub_withfrm extends mwmod_mw_ui_sub_uiabs{
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
	}

	/**
	 * Loads the required JavaScript files for the modern inputs system
	 * (jsobj_inputs + frmonpanel + helpers).
	 *
	 * Call from prepare_before_exec_no_sub_interface() in subclasses that
	 * render forms via mwmod_mw_jsobj_inputs_frmonpanel.
	 */
	protected function loadModernInputsJs(): void {
		$jsman = $this->maininterface->jsmanager;

		$jsman->add_item_by_cod("/res/js/util.js");
		$jsman->add_item_by_cod("/res/js/ajax.js");
		$jsman->add_item_by_cod("/res/js/url.js");
		$jsman->add_item_by_cod("/res/js/mw_date.js");
		$jsman->add_item_by_cod("/res/js/inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/inputs/container.js");
		$jsman->add_item_by_cod("/res/js/inputs/other.js");
		$jsman->add_item_by_cod("/res/js/inputs/date.js");
		$jsman->add_item_by_cod("/res/js/inputs/dx.js");
		$jsman->add_item_by_cod("/res/js/inputs/frm.js");
		$jsman->add_item_by_cod("/res/js/arraylist.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui.js");
		$jsman->add_item_by_cod("/res/js/mw_bootstrap_helper.js");
		$jsman->add_item_by_cod("/res/js/validator.js");

		$item = $this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}

	/**
	 * Renders a modern form panel into a UI container element by id.
	 *
	 * @param mwmod_mw_jsobj_inputs_frmonpanel $frm         The form panel object.
	 * @param string                            $containerId The container element id.
	 */
	protected function renderFormToContainer(mwmod_mw_jsobj_inputs_frmonpanel $frm, string $containerId): void {
		$container = $this->get_ui_dom_elem_container_empty();
		$frmContainer = $this->set_ui_dom_elem_id($containerId);
		$container->add_cont($frmContainer);
		$container->do_output();

		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var = $this->get_js_ui_man_name();

		$js->add_cont($var . ".init(" . $this->ui_js_init_params->get_as_js_val() . ");\n");
		$js->add_cont("var frm=" . $frm->get_as_js_val() . ";\n");
		$js->add_cont("frm.append_to_container(" . $var . ".get_ui_elem('" . $containerId . "'));\n");

		echo $js->get_js_script_html();
	}
}
?>