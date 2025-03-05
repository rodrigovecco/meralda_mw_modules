<?php
//
abstract class mwmod_mw_users_list_itemabs extends mw_apsubbaseobj{

	private $user_id;
	private $orig_user_id;
	private $final_user_id;
	
	private $user;
	private $orig_user;
	private $final_user;
	private $checked_user_ids=array();
	
	private $list;
	private $index;
	private $user_man;
	var $note;
	function process(){
		$this->set_users();	
	}
	function get_final_user_if_different(){
		if($this->final_user_id){
			if($this->final_user_id!=$this->orig_user_id){
				return $this->final_user;	
			}
		}
	}
	function on_no_user_allowed(){
		if($this->list){
			if($this->list->default_user){
				$this->set_final_user($this->list->default_user);	
			}
		}
	}
	function get_user_replacement($user){
		if($this->list){
			return $this->list->get_user_replacement($user,$this);	
		}
		
	}
	final function get_final_user_replacement($user){
		if($this->user_allowed($user)){
			return $user;	
		}
		$id=$user->get_id();
		if($this->checked_user_ids[$id]){
			return false;	
		}
		$this->checked_user_ids[$id]=$id;
		
		if(!$replacement=$this->get_user_replacement($user)){
			return false;	
		}
		return $this->get_final_user_replacement($replacement);
		
	}
	final function set_final_user($user){
		$this->final_user=$user;
		$this->final_user_id=$user->get_id();	
		$this->user=$user;
		$this->user_id=$user->get_id();	
	}
	function user_allowed($user){
		if($this->list){
			return $this->list->user_allowed($user,$this);	
		}
	}
	
	final function set_users(){
		if(!$uman=$this->__get_priv_user_man()){
			$this->on_no_user_allowed();
			return false;	
		}
		if(!$user=$uman->get_user($this->orig_user_id)){
			$this->on_no_user_allowed();
			return false;
		}
		$this->orig_user=$user;
		if($this->user_allowed($user)){
			$this->set_final_user($user);
			return true;
		}
		
		
		if($f=$this->get_final_user_replacement($user)){
			$this->set_final_user($f);
			return true;
		}
		$this->on_no_user_allowed();
		
		//$this->orig_user=
			
	}
	function get_debug_data(){
		$r=array();
		$r["orig_user_id"]=$this->orig_user_id;
		if($this->orig_user){
			$r["orig_user"]=$this->orig_user->get_real_and_idname();
		}
		$r["final_user_id"]=$this->final_user_id;
		if($this->final_user){
			$r["final_user"]=$this->final_user->get_real_and_idname();
		}
		return $r;
		
			
	}
	
	
	function get_debug_info(){
		$r=array(
			"user_id"=>$this->user_id,
			"orig_user_id"=>$this->orig_user_id,
		);
		return $r;
	}
	final function set_list($index,$list){
		$this->index=$index;
		$this->list=$list;
	}
	
	final function set_orig_user_id($id){
		//before check and load
		$this->unset_user();
		if($id=mw_get_number($id)){
			$this->user_id=$id;
			$this->orig_user_id=$id;
			return true;	
		}
	}
	
	final function unset_user(){
		unset($this->user);	
		unset($this->orig_user);	
		unset($this->final_user);	
		unset($this->user_id);	
		unset($this->orig_user_id);	
		unset($this->final_user_id);	
		$this->checked_user_ids=array();
	}
	
	
	final function __get_priv_user_id(){
		return $this->user_id;
	}

	final function __get_priv_orig_user_id(){
		return $this->orig_user_id;
	}

	final function __get_priv_final_user_id(){
		return $this->final_user_id;
	}

	final function __get_priv_user(){
		return $this->user;
	}

	final function __get_priv_orig_user(){
		return $this->orig_user;
	}

	final function __get_priv_final_user(){
		return $this->final_user;
	}

	final function __get_priv_checked_user_ids(){
		return $this->checked_user_ids;
	}

	final function __get_priv_list(){
		return $this->list;
	}

	final function __get_priv_index(){
		return $this->index;
	}

	
	final function __get_priv_user_man(){
		if(!isset($this->user_man)){
			if($this->list){
				if($this->list->user_man){
					$this->user_man=$this->list->user_man;
					return $this->user_man; 	
				}
			}
			if($man=$this->mainap->get_user_manager()){
				$this->user_man=$man;	
			}
		}
		return $this->user_man; 	
	}


}
?>