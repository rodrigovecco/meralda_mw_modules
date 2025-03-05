<?php
class mwmod_mw_ui_debug_financial_xirr extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("XIRR");
		
	}
	function do_exec_no_sub_interface(){
		
	}
	
	function do_exec_page_in(){
		if(!class_exists("mwmod_mw_financial_xirr")){
			echo "no existe mwmod_mw_financial_xirr";
			return;
		}
		$xirr=new mwmod_mw_financial_xirr();
		$xirr->add_amount('2012-7-9',-1000000);
		//$xirr->add_amount('2013-5-28',-240000);
		$xirr->add_amount('2013-5-28',-200000);
		$xirr->add_amount('2013-5-28',-40000);
		
		$xirr->add_amount('2013-7-30',-2000000);
		$xirr->add_amount('2013-11-18',-1960000);
		$xirr->add_amount('2014-5-28',-200000);
		$xirr->add_amount('2014-7-15',-2100000);
		$xirr->add_amount('2014-12-2',-1200000);
		$xirr->add_amount('2014-12-9',-6100000);
		$xirr->add_amount('2015-1-13',-1100000);
		$xirr->add_amount('2015-3-6',-3600000);
		$xirr->add_amount('2015-4-16',-1500000);
		$xirr->add_amount('2015-5-15',-1000000);
		$xirr->add_amount('2015-7-16',-1500000);
		$xirr->add_amount('2015-12-11',-3500000);
		$xirr->add_amount('2016-4-30',28825210.6172);
		
		//mw_array2list_echo($xirr->get_debug_data());
		mw_array2list_echo($xirr->get_debug_data_short());
		/*
		$xirr->set_guess(0.01);
		mw_array2list_echo($xirr->get_debug_data_short());
		$xirr->set_guess(0.001);
		mw_array2list_echo($xirr->get_debug_data_short());
		$xirr->set_guess(0.0001);
		mw_array2list_echo($xirr->get_debug_data_short());
		$xirr->set_guess(0.0001);
		mw_array2list_echo($xirr->get_debug_data_short());
		*/
		
		$xirr=new mwmod_mw_financial_xirr();
		$xirr->add_amount('2008-1-1',-10);
		$xirr->add_amount('2008-3-1',2.75);
		$xirr->add_amount('2008-10-30',4.25);
		$xirr->add_amount('2009-2-15',3.25);
		$xirr->add_amount('2009-4-1',2.75);
		mw_array2list_echo($xirr->get_debug_data());

		
		//echo "ddd";
	}

	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>