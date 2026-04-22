<?php
//20250207
class mwmod_mw_db_mysqli_dbman extends mwmod_mw_db_dbman{
	function __construct($ap){
		$this->init($ap);	
	}
	

	function create_views_managers() {
		$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
				WHERE TABLE_TYPE = 'VIEW' 
				AND TABLE_SCHEMA = DATABASE();";
	
		if (!$query = $this->query($sql)) {
			return false;
		}
	
		$r = [];
		while ($array = $this->fetch_array($query)) {
			if ($tbl = $array[0]) {
				if ($man = $this->create_view_man($tbl)) {
					$r[$tbl] = $man;
				}
			}
		}
	
		return !empty($r) ? $r : false;
	}
	
	function create_view_man_def($tbl) {
		if (!$tbl = $this->check_str_key($tbl)) {
			return false;
		}
		$man = new mwmod_mw_db_mysqli_view($this, $tbl);
		return $man;
	}
	

	



	function useAlwaysParameterizedMode(){
		return true;
	}
	function query($sql) {
		if (!$l = $this->get_link()) {
			return false;
		}

		try {
			// Soporte para paramQuery
			if (is_object($sql) && method_exists($sql, 'getSQL') && method_exists($sql, 'getParamsItems')) {
				
				$stmt = $l->prepare($sql->getSQL());
				
				if (!$stmt) {
					$this->lastException = new Exception($l->error);
					return false;
				}

				$params = $sql->getParamsItems();
				if (!empty($params)) {
					$types = '';
					$values = [];
					$__vals = [];

					foreach ($params as $p) {
						$types .= $p->getValueTypeCod();
						$val = $p->getValue();
						$__vals[] = $val;
						$values[] = &$__vals[array_key_last($__vals)];
					}

					array_unshift($values, $types);
					$bindResult = call_user_func_array([$stmt, 'bind_param'], $values);
					if ($bindResult === false) {
						$this->lastException = new Exception("bind_param failed: " . $stmt->error);
						return false;
					}
				}
				if (!$stmt->execute()) {
					$this->lastException = new Exception($stmt->error);
					return false;
				}

				$result = $stmt->get_result();
				return $result !== false ? $result : $stmt;
			}

			// Consulta directa (string)
			if (is_string($sql)) {
				$result = $l->query($sql);
				return ($result !== false) ? $result : false;
			}

		} catch (Exception $e) {
			
			$this->lastException = $e;
			return false;
		}

		return false;
	}
	
	function insert($sql) {
		if (!$l = $this->get_link()) {
			return false;
		}

		$result = $this->query($sql);
		if ($result === false) {
			return false;
		}

		$insertId = $l->insert_id;
		$affectedRows = $l->affected_rows;
		
		// Si insert_id es 0 pero affected_rows > 0, la inserción fue exitosa
		// (puede pasar con tablas sin auto-increment o con ID especificado)
		if ($insertId == 0 && $affectedRows > 0) {
			// Retornar true para indicar éxito aunque no haya insert_id
			return true;
		}
		
		// Si insert_id es 0 y affected_rows es 0 o negativo, verificar error
		if ($insertId == 0) {
			// Si no hay error de MySQL, la query se ejecutó pero no insertó
			// (ej: INSERT IGNORE con duplicate, o constraint que previene insert)
			if ($l->errno == 0) {
				// No hay error pero tampoco se insertó - retornar false
				return false;
			}
		}
		
		return $insertId;
	}














	//db methods
	function do_connect($cfg){
		if($v=$cfg["port"]??null){
			@$mysqli=new mysqli($cfg["host"]??null,$cfg["user"]??null,$cfg["pass"]??null,$cfg["db"]??null,$cfg["port"]??null );
		}else{
			@$mysqli=new mysqli($cfg["host"]??null,$cfg["user"]??null,$cfg["pass"]??null,$cfg["db"]??null);
		}
		if ($mysqli->connect_error) {
			return false;	
		}
		if($v=$cfg["charset"]??null){
			$mysqli->set_charset($cfg["charset"]);
		}
		/*
		if(!$v=$cfg["servermode"]??null){
			$mysqli->query("SET sql_mode=''");
		}
		*/
		if (!isset($cfg["servermode"]) || !$cfg["servermode"]) {
			//$mysqli->query("SET GLOBAL sql_mode=''");
			$mysqli->query("SET SESSION sql_mode=''");
		}
		return $mysqli;
		
		
			
	}
	function fetch_array($query){
		if(!$query){
			return false;	
		}
		if(!is_object($query)){
			return false;	
		}
		return $query->fetch_array(MYSQLI_BOTH);
		
	}
	
	function fetch_assoc($query){
		if(!$query){
			return false;	
		}
		if(!is_object($query)){
			return false;	
		}
		return $query->fetch_assoc();
		
		
	}
	function real_escape_string($txt){
		if(is_null($txt)){
			return "";	
		}
		if(!$l=$this->get_link()){
			return false;	
		}
		return $l->real_escape_string($txt);
		
		
	}
	function query_get_affected_rows($sql){
		if(!$l=$this->get_link()){
			return false;	
		}
		$this->query($sql);
		return $l->affected_rows;
		
	}
	function get_error(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		return $l->error;
		//return mysql_error($l);
			
	}
	function get_errorno(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		return $l->errno;
		//return mysql_errno($l);
			
	}
	function affected_rows(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		return $l->affected_rows;
		
			
	}
	

	
	
	
}
?>