<?php
//20241002
class mwmod_mw_db_sql_query extends mwmod_mw_db_sql_abs{
	private $select;
	private $from;
	private $where;
	private $group;
	private $order;
	private $limit;
	private $having;
	var $idfield="id";
	var $sql_count="count(*)";
	var $sql_count_name="num";
	
	public $linebreakmode=false;
	public $useFullQueryCount=false;

	public $parameterizedMode;

	public $currentParameterizedQuery;

	
	function __construct($from=false){
		if($from){
			$f=$this->__get_priv_from();
			$f->add_from($from);
		}
	}
	function isParameterizedMode(){
		if(isset($this->parameterizedMode)){
			return $this->parameterizedMode;
		}
		if($this->dbman){
			if($this->dbman->useAlwaysParameterizedMode()){
				return true;
			}
		}
		return false;
	}
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		$parts=$this->get_parts();
		foreach($parts as $part){
			if($this->debug_mode){
				$part->debug_mode=true;	
			}
			$part->append_to_parameterized_sql($pq);	
			if($this->debug_mode){
				$pq->appendSQL("\n");
			}

		}
	}
	function get_parameterized_sql(){
		$pq=new mwmod_mw_db_paramstatement_paramquery();
		$pq->sql="";
		$this->append_to_parameterized_sql($pq);
		$this->currentParameterizedQuery=$pq;
		return $pq;
	}



	function get_sql_or_parameterized_query(){
		if($this->isParameterizedMode()){
			return $this->get_parameterized_sql();
		}
		return $this->get_sql();
	}
	function get_array_from_sql(){
		if(!$sql=$this->get_sql_or_parameterized_query()){
			return false;	
		}
		return $this->dbman->get_array_from_sql($sql);
	}
	function get_array_data_from_sql_by_index(){
		////modificado 2024-03-06
		if(!$sql=$this->get_sql_or_parameterized_query()){
			return false;	
		}
		return $this->dbman->get_array_data_from_sql($sql,false);
	}
	
	function get_array_data_from_sql($idfield=false){
		if(!$idfield){
			$idfield=$this->idfield;	
		}
		if(!$sql=$this->get_sql_or_parameterized_query()){
			return false;	
		}
		return $this->dbman->get_array_data_from_sql($sql,$idfield);
	}
	function get_sql(){
		$sql="";
		$parts=$this->get_parts();
		foreach($parts as $part){
			if($this->debug_mode){
				$part->debug_mode=true;	
			}
			$sql.=$part->get_sql();	
			if($this->debug_mode){
				$sql.="\n";
			}

		}
		return $sql;
	}


	function get_count_sql_full_query_mode(){
		$sql="";
		$parts=array(
		"select"=>$this->__get_priv_select(),
		"from"=>$this->__get_priv_from(),
		"where"=>$this->__get_priv_where(),
		"group"=>$this->__get_priv_group(),
		"having"=>$this->__get_priv_having(),
		
		
		);
		foreach($parts as $part){
			$sql.=$part->get_sql();	

		}
		return "select count(*) as ".$this->sql_count_name." from ($sql) as zz";
			
	}
	function get_count_sql_parameterized_full_query_mode(){
		$pq=new mwmod_mw_db_paramstatement_paramquery();
		$pq->sql="";
		$pq->appendSQL("select count(*) as ".$this->sql_count_name." from (");
		$parts=array(
		"select"=>$this->__get_priv_select(),
		"from"=>$this->__get_priv_from(),
		"where"=>$this->__get_priv_where(),
		"group"=>$this->__get_priv_group(),
		"having"=>$this->__get_priv_having(),
		
		
		);
		foreach($parts as $part){
			$part->append_to_parameterized_sql($pq);

		}

		$pq->appendSQL(") as zz");
		return $pq;


	}
	
	function get_count_parameterized_sql(){
		if($this->useFullQueryCount){
			return $this->get_count_sql_parameterized_full_query_mode();	
		}


		$pq=new mwmod_mw_db_paramstatement_paramquery();
		$pq->sql="";

		$pq->appendSQL("select ".$this->sql_count." as ".$this->sql_count_name." ");
		$parts=array(
		"from"=>$this->__get_priv_from(),
		"where"=>$this->__get_priv_where(),
		//"having"=>$this->__get_priv_having(),
		
		);
		foreach($parts as $part){
			$part->append_to_parameterized_sql($pq);
			if($this->linebreakmode){
				$pq->appendSQL("\n\n");	
			}
		}
		return $pq;
	}

	function get_count_sql(){
		if($this->useFullQueryCount){
			return $this->get_count_sql_full_query_mode();	
		}
		
		
		
		
		$sql="select ".$this->sql_count." as ".$this->sql_count_name." ";
		$parts=array(
		"from"=>$this->__get_priv_from(),
		"where"=>$this->__get_priv_where(),
		//"having"=>$this->__get_priv_having(),
		
		);
		foreach($parts as $part){
			$sql.=$part->get_sql();	
			if($this->linebreakmode){
				$sql.="\n\n";	
			}
		}
		return $sql;
		
		
	}

	function get_count_sql_or_parameterized_query(){
		if($this->isParameterizedMode()){
			return $this->get_count_parameterized_sql();
		}
		return $this->get_count_sql();
	}

	function get_total_regs_num(){
		
		$sql=$this->get_count_sql_or_parameterized_query();
		//$sql=$this->get_count_sql();
		if(!$d=$this->dbman->get_array_from_sql($sql)){
			return 0;	
		}
		
		return $d[$this->sql_count_name]+0;
	}

	function execute(){


		//todo!!!!! if(!$sql=$this->get_sql_or_parameterized_query()){
		if(!$sql=$this->get_sql()){
			return false;	
		}
		return $this->dbman->query($sql);
			
	}
	function get_one_row_result(){
		
		if(!$sql=$this->get_sql_or_parameterized_query()){
			return false;	
		}

		return $this->dbman->get_array_from_sql($sql);
	}
	
	
	function fix_data_values(&$data,$date_keys){
		return $this->dbman->fix_data_values($data,$date_keys);	
	}
	function execute_debug(){
		if(!$sql=$this->get_sql()){
			return false;	
		}
		return $this->dbman->query_debug($sql);
			
	}
	
	function get_str_list_numeric($list){
		if(!$list){
			return false;	
		}
		if(!is_array($list)){
			$list=explode(",",$list."");	
		}
		if(!is_array($list)){
			return false;
		}
		$r=array();
		foreach($list as $id){
			if($id=mw_get_number($id)){
				$r[$id]=$id;	
			}
		}
		if(sizeof($r)){
			return implode(",",$r);	
		}
		
	}
	
	
	function get_parts(){
		$r=array(
		"select"=>$this->__get_priv_select(),
		"from"=>$this->__get_priv_from(),
		"where"=>$this->__get_priv_where(),
		"group"=>$this->__get_priv_group(),
		"having"=>$this->__get_priv_having(),
		"order"=>$this->__get_priv_order(),
		"limit"=>$this->__get_priv_limit(),
		
		
		);
		return $r;
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["dbmanclass"]=get_class($this->dbman);
		$r["sql"]=$this->get_sql();
		if($items=$this->get_parts()){
			
			$r["parts"]=array();
			foreach($items as $cod=>$item){
				$r["parts"][$cod]=$item->get_debug_data();	
			}
		}
		return $r;
			
	}

	
	//todas esta pueden cambiar por un iniciador
	final function __get_priv_limit(){
		if(!isset($this->limit)){
			$this->limit=new mwmod_mw_db_sql_limit($this);
		}
		return $this->limit; 	
	}
	final function __get_priv_from(){
		if(!isset($this->from)){
			$this->from=new mwmod_mw_db_sql_from($this);
		}
		return $this->from; 	
	}
	final function __get_priv_where(){
		if(!isset($this->where)){
			$this->where=new mwmod_mw_db_sql_where($this);
		}
		return $this->where; 	
	}
	final function __get_priv_select(){
		if(!isset($this->select)){
			$this->select=new mwmod_mw_db_sql_select($this);
		}
		return $this->select; 	
	}
	final function __get_priv_order(){
		if(!isset($this->order)){
			$this->order=new mwmod_mw_db_sql_order($this);
		}
		return $this->order; 	
	}
	final function __get_priv_group(){
		if(!isset($this->group)){
			$this->group=new mwmod_mw_db_sql_group($this);
		}
		return $this->group; 	
	}
	final function __get_priv_having(){
		if(!isset($this->having)){
			$this->having=new mwmod_mw_db_sql_having($this);
		}
		return $this->having; 	
	}

}
?>