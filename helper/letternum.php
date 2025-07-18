<?php
class mwmod_mw_helper_letternum extends mw_baseobj{
	private $_letters;
	function mwmod_mw_helper_letternum(){
	}
	
	function num2letter($num,$lower=false){
		$num=round($num+0);
		if($num<1){
			return "";	
		}
		$l_num=$this->get_letters_num();
		$alpha      = '';
		if($num <= $l_num){
			$alpha =  $this->get_letter($num);
		} elseif($num >$l_num) {
			$dividend   = ($num);
			$alpha      = '';
			
			while($dividend > 0){
				$modulo     = ($dividend -1) % $l_num;
				$alpha      =  $this->get_letter($modulo+1).$alpha;
				$dividend   = floor((($dividend - $modulo) / $l_num));
				//$dividend   = ceil((($dividend - $modulo) / $l_num));
			}
    	}
		if($lower){
			return strtolower($alpha);	
		}else{
			return $alpha;	
		}
	}
	final function get_letters_num(){
		$this->init_letters();
		return sizeof($this->_letters);	
	}
	
	final function get_letters(){
		$this->init_letters();
		return $this->_letters;	
	}
	final function get_letter($index){
		$this->init_letters();
		return $this->_letters[$index];	
	}
	
	final function init_letters(){
		if(isset($this->_letters)){
			return;	
		}
		$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$this->_letters = array();
		$x=0;
		foreach($letters as $l){
			$x++;
			$this->_letters[$x]=$l;	
		}
		
	}

	
	
}
?>