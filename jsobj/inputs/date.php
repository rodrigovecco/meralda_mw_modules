<?php
class mwmod_mw_jsobj_inputs_date extends mwmod_mw_jsobj_inputs_input {

	function __construct($cod, $objclass = false){
		$this->def_js_class="mw_datainput_item_date";
		$this->init_js_input($cod, $objclass);
		
	}

	// 📆 ===== Propiedades específicas de fecha =====

	function setMinDate($val){
		// Valor mínimo permitido (string: "YYYY-MM-DD" o timestamp)
		$this->set_prop("mindate", $val);
		return $this;
	}

	function setMaxDate($val){
		// Valor máximo permitido
		$this->set_prop("maxdate", $val);
		return $this;
	}

	function setNoHour($v = true){
		// Solo fecha (sin selector de hora)
		$this->set_prop("nohour", $v);
		return $this;
	}

	function setTimeOnly($v = true){
		// Solo hora (sin selector de fecha)
		$this->set_prop("timeonly", $v);
		return $this;
	}

	function setShowPickerOnFocus($v = true){
		// Abre el calendario automáticamente al enfocar
		$this->set_prop("showpickeronfocus", $v);
		return $this;
	}

	
}
