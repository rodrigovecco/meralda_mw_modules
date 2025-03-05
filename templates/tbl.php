<?php
class mwmod_mw_templates_tbl extends mw_apsubbaseobj{
	private $cols_cods;
	public $tr_bg_colors=array("#D6DEE8","#7E9BBB");
	public $tr_bg_color_next_index=0;
	public $html;

	function __construct(){
		$this->set_mainap();
	}
	function order_cols($data){
		if(!is_array($data)){
			return false;
		}
		if(!$cols=$this->get_cols_cods()){
			return false;
		}
		$r=array();
		foreach($cols as $cod){
			$r[$cod]=$data[$cod];	
		}
		return $r;
	}
	function get_row_ordered($data){
		if(!$data=$this->order_cols($data)){
			return false;
		}
		$html=$this->get_next_tr_open();
		$html.=$this->get_row_cont($data);
		
		/*
		foreach($data as $cod=>$d){
			$html.=$this->get_td_open().$d.$this->get_td_close();	
		}
		*/
		$html.=$this->get_tr_close();
		return $html;
	}
	function get_row_cont($data){
		$html="";
		foreach($data as $cod=>$d){
			$html.=$this->get_td_open().$d.$this->get_td_close();	
		}
		return $html;
			
	}
	function get_row($data){
		if(!is_array($data)){
			return false;
		}
		$html=$this->get_next_tr_open();
		$html.=$this->get_row_cont($data);

		/*
		foreach($data as $cod=>$d){
			$html.=$this->get_td_open().$d.$this->get_td_close();	
		}
		*/
		$html.=$this->get_tr_close();
		return $html;
	}

	function get_tbl_open_header_and_set_cols_cods($data){
		if(!is_array($data)){
			return false;
		}
		$html=$this->get_tbl_open();
		$html.=$this->get_row_header_and_set_cols_cods($data);
		return $html;
			
	}
	function get_row_header_and_set_cols_cods($data){
		if(!is_array($data)){
			return false;
		}
		$this->set_cols_cods(array_keys($data));
		return $this->get_row_header($data);
		
	}

	function get_row_header($data){
		if(!is_array($data)){
			return false;
		}
		$html=$this->get_tr_open();
		foreach($data as $cod=>$d){
			$html.=$this->get_th_open().$d.$this->get_th_close();	
		}
		$html.=$this->get_tr_close();
		return $html;
	}
	function get_next_tr_bg_color(){
		$r=$this->tr_bg_colors[$this->tr_bg_color_next_index];
		$this->tr_bg_color_next_index++;
		if($this->tr_bg_color_next_index>=sizeof($this->tr_bg_colors)){
			$this->tr_bg_color_next_index=0;	
		}
		return $r;
	}
	
	final function set_cols_cods($keys){
		$this->cols_cods=$keys;
	}
	final function get_cols_cods(){
		return $this->cols_cods;
	}
	
	function get_th_open(){
		return "<th>";
			
	}
	function get_next_tr_open(){
		return "<tr style='background-color:".$this->get_next_tr_bg_color()."'>\n";
			
	}

	function get_th_close(){
		return "</th>";
			
	}
	function get_td_open(){
		return "<td>";
			
	}
	function get_td_close(){
		return "</td>";
			
	}
	
	function get_tr_open(){
		return "<tr>\n";
			
	}
	function get_tr_close(){
		return "</tr>\n";
			
	}
	
	function get_tbl_open(){
		return "<div class='list_tbl'><table rules='cols' width='100%' border='1' bordercolor='#FFFFFF' cellpadding='3' cellspacing='1'>\n";
			
	}
	function get_tbl_close(){
		return "</table></div>\n";
			
	}
	
	
	function __call($a,$b){
		return false;	
	}
	
}
?>