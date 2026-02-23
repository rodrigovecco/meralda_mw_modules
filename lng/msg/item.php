<?php
/**
 * Represents a single language message entry identified by a code.
 *
 * @property-read string                     $cod  Unique message code.
 * @property-read mwmod_mw_lng_msg_lngmanabs $man  Parent language manager instance.
 */
class  mwmod_mw_lng_msg_item extends mw_apsubbaseobj{
	private $cod;
	private $man;//mwmod_mw_lng_msg_lngmanabs
	var $msg="";
	/**
	 * @param string                     $cod  Unique message code.
	 * @param mwmod_mw_lng_msg_lngmanabs $man  Parent language manager.
	 */
	function __construct($cod,$man){
		$this->init($cod,$man);	
	}
	/**
	 * Returns the serialised line for writing to a language text file.
	 *
	 * @return string  Format: "<code> <escaped-message>"
	 */
	function get_txt_file_line(){
		return $this->cod." ".$this->escape_msg_for_storage();	
	}
	/**
	 * Returns the message with \t, \n, \r escaped for single-line file storage.
	 *
	 * @return string
	 */
	function escape_msg_for_storage(){
		$search = array("\t", "\n", "\r");
    	$replace = array('\t', '\n', '\r');
    	return str_replace($search, $replace, $this->msg);
	}
	/**
	 * Returns the message text with optional %placeholder% substitution.
	 *
	 * @param  array|false $params  Associative array of replacements, or false.
	 * @return string
	 */
	function get_msg_txt($params=false){
		$msg=$this->msg;
		if(is_array($params)){
			foreach($params as $cod=>$v){
				$msg=str_replace("%{$cod}%",$v,$msg);
			}
		}
		return $msg;
	}

	/**
	 * Sets the raw message text.
	 *
	 * @param  string $msg
	 * @return void
	 */
	function set_msg($msg){
		$this->msg=$msg;
	}
	/**
	 * @internal Accessed via magic property $cod.
	 * @return string
	 */
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	/**
	 * @internal Accessed via magic property $man.
	 * @return mwmod_mw_lng_msg_lngmanabs
	 */
	final function __get_priv_man(){
		return $this->man; 	
	}

	/**
	 * Initialises the item with its code and parent manager.
	 *
	 * @param  string                     $cod
	 * @param  mwmod_mw_lng_msg_lngmanabs $man
	 * @return void
	 */
	final function init($cod,$man){
		$this->man=$man;
		$this->cod=$cod;
		
	}

}
?>