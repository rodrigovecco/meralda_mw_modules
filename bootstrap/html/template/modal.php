<?php
class mwmod_mw_bootstrap_html_template_modal extends mwmod_mw_bootstrap_html_template_abs{
	var $fade;
	var $small;
	var $id;
	var $title;//sólo usada al create_cont
	var $openbtn;
	function __construct($id="mymodal",$title=false,$fade=true,$small=false){
		
		$main=new mwmod_mw_bootstrap_html_specialelem_modal($id,$fade,$small);
		$this->fade=$fade;
		$this->small=$small;
		$this->title=$title;
		$this->id=$id;

		$this->create_cont($main);
		
	}
	function set_open_btn($btn=false){
		if(!$btn){
			$bnt=$this->create_open_btn();	
		}
		if(!$btn){
			return false;	
		}
		$this->set_key_cont("openbtn",$btn);
		$this->openbtn=$btn;
		$this->update_other_elems();
		return $this->openbtn;
		
	}
	function create_open_btn($cont=false,$display_mode="primary"){
		$btn=new mwmod_mw_bootstrap_html_specialelem_btn($cont,$display_mode);
		$btn->set_att("data-toggle","modal");
		$btn->set_att("data-bs-toggle","modal");
		return $btn;
	}
	function new_open_btn($cont=false,$display_mode="primary"){
		$btn=$this->create_open_btn($cont,$display_mode);
		return $this->set_open_btn($btn);
	}
	
	function get_open_btn(){
		if($this->openbtn){
			return $this->openbtn;	
		}
		if($btn=$this->create_open_btn()){
			return $this->set_open_btn($btn);	
		}
	}
	function update_other_elems(){
		if($elem=$this->main_elem){
			if($this->id){
				$elem->set_id($this->id);
			}
			$elem->set_fade($this->fade);
			$elem->set_small($this->small);
		}
		if($elem=$this->get_key_cont("dialog")){
			$elem->set_small($this->small);
		}
		if($elem=$this->openbtn){
			if($this->id){
				$elem->set_att("data-target","#".$this->id);
				$elem->set_att("data-bs-target","#".$this->id);
			}
		}
		
	}
	function set_id($id){
		$this->id=$id;
		$this->update_other_elems();
	}
	function set_fade($val=true){
		$this->fade=$val;
		$this->update_other_elems();
	}
	function set_small($val=true){
		$this->small=$val;
		$this->update_other_elems();
	}
	
	
	function create_cont($main){
		$this->set_main_elem($main);
		$dialog=new mwmod_mw_bootstrap_html_specialelem_modaldialog($this->small);
		$main->add_cont($dialog);
		$this->set_key_cont("dialog",$dialog);
		$modalcontent=new mwmod_mw_bootstrap_html_def("modal-content");
		$dialog->add_cont($modalcontent);
		$this->set_key_cont("modalcontent",$modalcontent);
		
		$head=new mwmod_mw_bootstrap_html_def("modal-header");
		$modalcontent->add_cont($head);
		$this->set_key_cont("modalheader",$head);
		
		
		$headtitle=new mwmod_mw_bootstrap_html_def("modal-title","h4");
		if($this->title){
			$headtitle->add_cont($this->title);	
		}
		$this->set_title_elem($headtitle);
		$head->add_cont($headtitle);
		
		$btn_close=new mwmod_mw_bootstrap_html_def("btn-close","button");
		// <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

		$btn_close->set_att("type","button");
		$btn_close->set_att("data-bs-dismiss","modal");
		$btn_close->set_att("aria-label","Close");
		//$btn_close->add_cont("<span aria-hidden='true'><i class='fa fa-window-close'> </i></span>");
		$this->set_key_cont("btnclose",$btn_close);
		$head->add_cont($btn_close);
	
		$body=new mwmod_mw_bootstrap_html_def("modal-body");
		$this->set_cont_elem($body);
		$modalcontent->add_cont($body);
		$footer=new mwmod_mw_bootstrap_html_def("modal-footer");
		$this->set_key_cont("footer",$footer);
		$footer->only_visible_when_has_cont=true;
		$modalcontent->add_cont($footer);
		$this->update_other_elems();
		
		
	}
	

}

?>