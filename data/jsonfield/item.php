<?php
class mwmod_mw_data_jsonfield_item extends mwmod_mw_data_json_item{
	private $tblitem;
	
	function __construct($tblitem,$code){
		$this->initAsTBLField($tblitem,$code);	
	}
	final function initAsTBLField($tblitem,$code){
		$this->tblitem=$tblitem;
		$this->setCode($code);
		
		
	}
	function encode($value){
		//json_encode ( mixed $value [, int $options = 0 [, int $depth = 512 ]] )	
		return self::encode_value($value);
	}
	/**
	 * Encodes a value for a JSON COLUMN, without an instance — for the moment a row is being CREATED.
	 *
	 * WHY THIS EXISTS. A JSON object in a column is normally written through this item, which needs a row.
	 * `insert_item(array("config" => json_encode($config)))` happens BEFORE that row exists, so the writer has
	 * no item to ask — and reaching for `json_encode` there is how a module ends up with two encoders that
	 * disagree about options and about what an empty value should be.
	 *
	 * The instance method `encode()` delegates here, so there is exactly one encoder for this shape and a
	 * create path and an update path cannot produce different bytes for the same array.
	 *
	 * @param  mixed $value
	 * @return string
	 */
	static function encode_value($value){
		return (string) json_encode(is_array($value) ? $value : array());
	}
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		$this->load_data_once();
		$item=new mwmod_mw_data_jsonfield_subitem($key,$this);
		return $item;
	
	}
	function save(){
		$this->modified=false;
		$data=$this->_get_data();
		$string=$this->encode($data);
		if(!$cod=$this->getTblFieldCode()){
			return false;
		}

		$nd[$cod]=$string;
		if(!$this->tblitem){
			return false;
		}
		$this->tblitem->update($nd);

		return true;
	}
	function getTblFieldCode(){
		return $this->code;
	}
	function get_data_to_load(){
		if($this->tblitem){
			if($cod=$this->getTblFieldCode()){
				if($string=$this->tblitem->get_data($cod)){
					if($data=$this->decode($string)){
						if(is_array($data)){
							return $data;
							//return true;	
						}
					}
				}
			}
		}




			
	}
	final function __get_priv_tblitem(){
		return $this->tblitem;
	}

}


?>