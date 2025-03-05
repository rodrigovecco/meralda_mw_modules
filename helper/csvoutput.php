<?php

class mwmod_mw_helper_csvoutput extends mwmod_mw_templates_tbl{
	var $file_handler;
	var $file_name="table.csv";
	var $sep=";";
	
	var $utf8mode=false;// iso-8859-1
	
	function __construct(){
		
	}
	function is_utf8mode(){
		return $this->utf8mode;	
	}
	function get_row_data($data){
		if(!is_array($data)){
			$d=$data;
			$data=array($d);	
		}
		if($this->is_utf8mode()){
			return $data;
		}
		$r=array();
		foreach($data as $cod=>$d){
			if(is_string($d)){
				$d=utf8_decode($d);	
			}
			$r[$cod]=$d;
		}
		return $r;
		
	}
	function write_row($data){
		if(!$data=$this->get_row_data($data)){
			return false;	
		}
		if(!$h=$this->get_file_handler()){
			return false;	
		}
		return fputcsv ( $h , $data , $this->sep);
		
	}
	
	function write_row_ordered($data){
		if(!$data=$this->order_cols($data)){
			return false;
		}
		return $this->write_row($data);
	}
	
	function write_titles_and_set_cols_cods($data){
		if(!is_array($data)){
			return false;
		}
		$this->set_cols_cods(array_keys($data));
		return $this->write_row($data);
		
	}
	
	function write_sep(){
		if(!$h=$this->get_file_handler()){
			return false;	
		}
		//return fwrite($h,"\xEF\xBB\xBFsep=".$this->sep."\n");
		return fwrite($h,"sep=".$this->sep."\n");
	}
	function set_file_handler($handler){
		if(!$handler){
			return false;	
		}
		if(is_string($handler)){
			$handler=	fopen($handler, 'w');	
		}
		if(!$handler){
			return false;	
		}
		
		$this->file_handler=$handler;
		return $handler;
	}
	function unset_file_handler(){
		unset($this->file_handler);	
	}
	function close_file_handler(){
		if($h=$this->get_file_handler()){
			fclose($h);	
			$this->unset_file_handler();
			return true;
		}
		
	}
	function set_file_name($name){
		$this->file_name=$name;
	}
	function set_output_handler(){
		return $this->set_file_handler(fopen('php://output', 'w'));	
	}
	function get_file_handler(){
		return $this->file_handler;	
	}
	
	function output_headers(){
		ob_end_clean();
		if($this->is_utf8mode()){
			header('Content-Type: text/csv; charset=utf-8');
		}else{
			header('Content-Type: text/csv; charset=iso-8859-1');	
		}
		header('Content-Disposition: attachment; filename='.$this->file_name);	
	}
	
}
?>