<?php
class mwmod_mw_jsobj_script extends mwmod_mw_jsobj_codecontainer{
	function __construct($jscode=false){
		$this->add_cont($jscode);
		$this->outputAsScriptOnHTML=true;
	}
}
?>