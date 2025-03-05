<?php
class mwmod_mw_db_paramstatement_param extends mw_apsubbaseobj{
	public $value=null;
	public $statementMan;
	public $field;//used for inserts and updated.
	function __construct($val,$statementMan){
		$this->setStatementMan($statementMan);
		$this->setValue($val);
	}
	function setStatementMan($statementMan){
		return $this->statementMan=$statementMan;
	}
	function setValue($val){
		return $this->value=$val;
	}
	function getValueRaw(){
		return $this->value;
	}
	function getValue(){
		//depending onf type todo
		return $this->getValueRaw();
	}
	function getValueTypeCod(){
		//todo check for field data type
		$value=$this->getValueRaw();
		if (is_int($value)) {
        	return 'i'; // Integer
	    } elseif (is_float($value)) {
	        return 'd'; // Double (floating-point number)
	    } elseif (is_string($value)) {
	        return 's'; // String
	    } elseif (is_bool($value)) {
	        return 'i'; // Boolean (treat as integer)
	    } elseif (is_null($value)) {
	        return 's'; // Null (treat as string)
	    } elseif (is_resource($value) && get_resource_type($value) === 'stream') {
        	return 'b'; // Binary data (stream resource)
	    } else {
	        return "s";
	        //return false; // Unsupported type
	    }
	}
	function getDebugData(){
		$r=array();
		$r["value"]=$this->getValue();
		$r["valueRaw"]=$this->getValueRaw();
		$r["type"]=$this->getValueTypeCod();
		return $r;
	}
	

}

