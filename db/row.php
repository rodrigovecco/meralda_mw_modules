<?php
class  mwmod_mw_db_row extends mw_apsubbaseobj{
	private $tblman;
	private $id;
	private $data=array();
	private $datatoupdate=array();
	function __construct($id,$data,$tblman){
		$this->init($id,$data,$tblman);	
	}
	function format_time($time=true){
		return $this->tblman->format_time($time);
	}
	function real_escape_string($txt){
		return $this->tblman->real_escape_string($txt);	
	}
	
	function delete(){
		if(!$idfield=$this->tblman->id_field){
			return false;
		}
		if(!$id=$this->get_id()){
			return false;	
		}

		//
		$sql="delete from ".$this->tblman->tbl;
		if($this->tblman->dbman->dbModeCheckSQLsrv()){
			$sql="delete TOP (1) from ".$this->tblman->tbl;
			$sql.=" where $idfield=$id ";
		}else{
			$sql.=" where $idfield=$id limit 1";
		}
		

		$this->tblman->dbman->query($sql);
		return true;
			
	}
	final function directSetData($cod,$val){
		$this->data[$cod]=$val;
	}
	//20240313
	function get_update_sql($data){
		return $this->generate_update_sql($data);
	}
	function generate_update_sql($data,$paramQuery=false){
		if(!is_array($data)){
			return false;	
		}
		if(!$idfield=$this->tblman->id_field){
			return false;
		}
		unset($data[$idfield]);
		
		if(!$fields=$this->tblman->get_tbl_fields()){
			return false;	
		}
		
		
		$updatelist=array();
		$updatelistPH=array();
		$ok=false;
		if(!$id=$this->get_id()){
			return false;	
		}
		foreach($data as $c=>$v){
			//20231206
			if(is_string($c)){
				if(!is_array($v)){
					if($field=$this->tblman->getField($c)){
						//20241113
						if(is_object($v)){
							if(is_a($v, "mwmod_mw_db_sql_value_abs")){
								$this->data[$c]=$v->getValueAsData();
								$cc="`$c`";

								if($this->tblman->dbman->dbModeCheckSQLsrv()){
									$cc="$c";
								}
								$updatelist[]=$cc."=".$v->getSQLValueStrQuoted()." ";
								if(($paramQuery)and($v->isParamQueryAllowed())){
									$updatelistPH[]=$cc."=? ";
									$paramQuery->addParam($v->getValueForParamQuery());
								}else{
									$updatelistPH[]=$cc."=".$v->getSQLValueStrQuoted()." ";
								}


								$ok=true;
							}


						}elseif((!$this->tblman->only_update_if_different)or(($this->data[$c]!==$v)or(!isset($this->data[$c])))){
							$this->data[$c]=$v;
							if(is_null($v) and $field->nullAllowed()){
								if($this->tblman->dbman->dbModeCheckSQLsrv()){
									$updatelist[]="$c=NULL ";
									$updatelistPH[]="$c=? ";
								}else{
									$updatelist[]="`$c`=NULL ";
									$updatelistPH[]="`$c`=? ";
								}
								
								if($paramQuery){
									$paramQuery->addParam(null);
								}
							}else{
								if($this->tblman->dbman->dbModeCheckSQLsrv()){
									$updatelist[]="$c='".$this->real_escape_string($v)."'";
									$updatelistPH[]="$c=? ";
								}else{
									$updatelist[]="`$c`='".$this->real_escape_string($v)."'";
									$updatelistPH[]="`$c`=? ";
								}
								
								if($paramQuery){
									$paramQuery->addParam($v);
								}
							}
							
							$ok=true;
						}


					}
					


					
				}
			}
		}
		if(!$ok){
			return false;	
		}
		if($paramQuery){
			$paramQuery->addParam($id);
		}
		if($this->tblman->dbman->dbModeCheckSQLsrv()){
			$sql="update TOP (1)  ".$this->tblman->tbl;
			if($paramQuery){
				$paramQuery->appendSQL($sql);
				$paramQuery->appendSQL(" set ".implode(",",$updatelistPH)." ");
				$paramQuery->appendSQL("where $idfield =? ");
			}
			$sql.=" set ".implode(",",$updatelist)." ";
			$sql.="where $idfield=$id ";

		}else{
			$sql="update ".$this->tblman->tbl;
			if($paramQuery){
				$paramQuery->appendSQL($sql);
				$paramQuery->appendSQL(" set ".implode(",",$updatelistPH)." ");
				$paramQuery->appendSQL("where $idfield =?  limit 1");
			}
			$sql.=" set ".implode(",",$updatelist)." ";
			$sql.="where $idfield=$id limit 1";
		}
		
		return $sql;
	}

	final function do_update($data){
		$paramQuery=false;

		if($this->tblman->dbman->useAlwaysParameterizedMode()){
			$paramQuery=new mwmod_mw_db_paramstatement_paramquery();
		}

		if(!$sql=$this->generate_update_sql($data,$paramQuery)){
			return false;
		}

		if($this->tblman->dbman->useAlwaysParameterizedMode()){
			$this->tblman->dbman->query($paramQuery);
		}else{
			$this->tblman->dbman->query($sql);
		}
		
		return true;

			
	}
	function update($data){
		if(!is_array($data)){
			return false;	
		}
		if($readonly=$this->tblman->get_read_only_fields()){
			foreach($readonly as $cod){
				unset($data[$cod]);	
			}
		}
		
		
		return $this->do_update($data);
		
		
	}
	function update_only_listed_fields($data,$allowed){
		if(!is_array($data)){
			return false;	
		}
		if(!is_array($allowed)){
			$allowed_str=$allowed."";
			$allowed=explode(",",$allowed_str);	
		}
		reset($data);
		$nd=array();
		foreach($data as $cod=>$val){
			if(in_array($cod,$allowed)){
				$nd[$cod]=$val;
			}
		}
		if(!sizeof($nd)){
			return false;	
		}
		return $this->do_update($nd);
		
		
	}
	function get_date_js($key,$format="Y/m/d H:i:s"){
		return $this->get_formated_date($key,$format);
	}
	function get_formated_date($key,$format="Y-m-d"){
		if(!$d=$this->get_data_as_date($key)){
			return "";	
		}
		return date($format,strtotime($d));
	}
	function get_data_as_time($key){
		if(!$d=$this->get_data_as_date($key)){
			return false;	
		}
		return strtotime($d);
		
	}

	function get_data_as_date($key){
		if(!$s=$this->get_data($key)){
			return "";	
		}
		if($s=="0000-00-00"){
			return "";	
		}
		if($s=="0000-00-00 00:00:00"){
			return "";	
		}
		return $s;
		
	}
	
	final function get_data($key=""){
		return mw_array_get_sub_key($this->data,$key);	
	}
	final function get_id(){
		return $this->id;		
	}
	final function get_isset_data($key){
		if(!$key){
			return false;
		}
		return array_key_exists($key,$this->data);
	}

	final function get_tbl(){
		return $this->tblman->tbl;		
	}
	function __toString(){
		return $this->get_tbl()." (".$this->get_id().")";
	}
	final function __get_priv_tblman(){
		return $this->tblman; 	
	}
	final function set_extra_data($data){
		if(!is_array($data)){
			return false;	
		}
		$n=0;
		foreach($data as $cod=>$v){
			if(!array_key_exists($cod,$this->data)){
				$this->data[$cod]=$v;
			}
		}
		return $n;
	}
	
	final function init($id,$data,$tblman){
		$ap=$tblman->mainap;
		$this->tblman=$tblman;
		$this->data=$data;
		$this->id=$id+0;
		
		$this->set_mainap($ap);	
	
	}
	
}

?>