<?php
class mwmod_mw_db_sqlsrv_dbman extends mwmod_mw_db_mysqli_dbman{
	public $serverName;
	public $dbName;
	function __construct($ap){
		$this->init($ap);	
	}
	function create_views_managers(){
		$sql = "SELECT TABLE_NAME FROM [INFORMATION_SCHEMA].TABLES WHERE TABLE_TYPE = 'VIEW';";
    	if(!$query=$this->query($sql)){
			return false;	
		}
		$r=array();
		if ($array=$this->fetch_array($query)){
			do{
				if($tbl=$array[0]){
					if($man=$this->create_view_man($tbl)){
						$r[$tbl]=$man;	
					}
				}
			}while ($array=$this->fetch_array($query));
		
		}
		if(sizeof($r)){
			return $r;	
		}
		
			
	}
	function create_view_man_def($tbl){
		
		if(!$tbl=$this->check_str_key($tbl)){
			return false;	
		}
		$man=new mwmod_mw_db_sqlsrv_view($this,$tbl);
		return $man;
		
			
	}


	
	function dbModeCheck($mode){
		if($mode=="sqlsrv"){
			return true;
		}
		return false;	
	}
	function useAlwaysParameterizedMode(){
		return true;
	}
	function create_tbl_managers(){
		//$sql = "SELECT TABLE_NAME FROM [".$this->dbName."].[INFORMATION_SCHEMA].TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG = '".$this->dbName."'";

		$sql = "SELECT TABLE_NAME FROM [INFORMATION_SCHEMA].TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
    	if(!$query=$this->query($sql)){
			return false;	
		}
		$r=array();
		if ($array=$this->fetch_array($query)){
			do{
				if($tbl=$array[0]){
					if($man=$this->create_tbl_man($tbl)){
						$r[$tbl]=$man;	
					}
				}
			}while ($array=$this->fetch_array($query));
		
		}
		if(sizeof($r)){
			return $r;	
		}
			
	}
	function create_tbl_man_def($tbl){
		if(!$tbl=$this->check_str_key($tbl)){
			return false;	
		}
		$man=new mwmod_mw_db_sqlsrv_tbl($this,$tbl);
		return $man;
			
	}
	//db methods
	function do_connect($cfg){
		$connectionInfo = array(
	        "UID" => $cfg["user"] ?? null,
	        "PWD" => $cfg["pass"] ?? null,
	        "Database" => $cfg["db"] ?? null,
	        'ReturnDatesAsStrings'=> true
	    );
	    if(isset($cfg["CharacterSet"])){
	    	$connectionInfo["CharacterSet"]=$cfg["CharacterSet"];
	    }
		$this->serverName=$cfg["host"];
		$this->dbName=$cfg["db"];
	    if(isset($cfg["port"])){
	        $serverName = $cfg["host"] . "," . $cfg["port"];
	    } else {
	        $serverName = $cfg["host"];
	    }

	    // Establish the connection
	    $conn = sqlsrv_connect($serverName, $connectionInfo);

	    // Check if the connection was successful
	    if ($conn === false) {
	        return false;
	    }

	    if(isset($cfg["charset"])){
	        // Set the character set
	       // sqlsrv_client_info($conn)["CharacterSet"];
	    }

    	return $conn;
		
		
			
	}
	function query($sql){



	    if(!$l = $this->get_link()) {
	        return false;
	    }
	    
	    try {
	    	if(is_string($sql)){

	    		 $result = sqlsrv_query($l, $sql);
	    	}elseif(is_object($sql)){
	    		 $result = sqlsrv_query($l, $sql->getSQL(),$sql->getParams());
	    	}

	       

	        if($result === false) {
	        	
	            return false;
	        }
	        
	        return $result;
	    } catch (Exception $e) {
	        $this->lastException = $e;
	        return false;
	    }
    }
	function fetch_array($query){
		
		if(!$query){
		    return false;  
		}


		if(!is_resource($query)){
		    return false;  
		}
		

		return sqlsrv_fetch_array($query, SQLSRV_FETCH_BOTH);		
	}
	
	function fetch_assoc($query){
		if(!$query){
		    return false;  
		}

		if(!is_resource($query)){
		    return false;  
		}

		return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
	}
	function real_escape_string($txt){
		// Remove potentially dangerous characters
		//added only for legacy. It is better to use parameterized queries!!!!!
    	$safe_txt = preg_replace('/[^\p{L}\p{N}@_-]/u', '', $txt);	
    	return $safe_txt;

		
	}
	function insert($sql){
	    // Assuming $this->get_link() returns a valid connection
	    $l = $this->get_link();
	   
	    if (!$l) {
	        return false;
	    }
	    

	    if(is_string($sql)){
	    	 $result = sqlsrv_query($l, $sql);
	    }elseif(is_object($sql)){
	    	 $result = sqlsrv_query($l, $sql->getSQL(),$sql->getParams());

	    }
	   	
	    if(!$result){
	    	
	    	return false;
	    }
	    $stmt = sqlsrv_query($l, "select last_insert_id=@@identity");
	    if ($stmt !== false) {
	        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
	        if ($row !== false) {
	            return $row[0];
	        }
	    }
	    return false;

	  
	    
	    
	    return false;
		
	}
	function query_get_affected_rows($sql){
	    // Assuming $this->get_link() returns a valid connection
	    $conn = $this->get_link();
	    
	    if (!$conn) {
	        return false;
	    }
	    
	    // Execute the query
	    $result = $this->query($sql);
	    
	    // Check if the query execution was successful
	    if ($result !== false) {
	        // Retrieve the affected rows count
	        $stmt = sqlsrv_query($conn, "SELECT @@ROWCOUNT");
	        if ($stmt !== false) {
	            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
	            if ($row !== false) {
	                return $row[0];
	            }
	        }
	    }
	    
	    return false;
		
	}
	function get_error() {
	    // Assuming $this->get_link() returns a valid connection
	    $conn = $this->get_link();
	    
	    if (!$conn) {
	        return false;
	    }
	    
	    $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
	    
	    if ($errors !== null) {
	        // Return the first error message
	        return $errors[0]['message'];
	    }
	    
	    return false;
	}

	function get_errorno() {
	    // Assuming $this->get_link() returns a valid connection
	    $conn = $this->get_link();
	    
	    if (!$conn) {
	        return false;
	    }
	    
	    $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
	    
	    if ($errors !== null) {
	        // Return the first error code
	        return $errors[0]['code'];
	    }
	    
	    return false;
	}

	function affected_rows() {
	    // Assuming $this->get_link() returns a valid connection
	    $conn = $this->get_link();
	    
	    if (!$conn) {
	        return false;
	    }
	    
	    // Get the number of affected rows
	    $rows_affected = sqlsrv_rows_affected($conn);
	    
	    if ($rows_affected !== false) {
	        return $rows_affected;
	    }
	    
	    return false;
	}	

	
	
	
}
?>