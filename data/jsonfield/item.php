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
		return json_encode($value);
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