<?php
class mwmod_mw_paymentapi_api_culqi_debugui_testapi extends mwmod_mw_paymentapi_debugui_mod_testapi{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Probar api culqi");
		
	}

	
	function do_exec_page_in(){
		if(!$m=$this->getCurrentModule()){
			"Invalid module";	
			return false;
		}
		if(!$api=$m->newApi()){
			"Invalid api";	
			return false;
				
		}
		//$key=$m->get_key_item("privatekey")->get_data();
		//echo "<p>$key</p>";
		
		
		$html=new mwmod_mw_html_elem();
		
		$e=$html->add_cont_elem();
		$e->add_cont_elem(get_class($api));
		$api->debugTestApiClassesLoaded();
		
		if(!$culqi=$api->createCulqi()){
			echo $html;
		
			return false;	
		}
		
		$fnc=new mwmod_mw_jsobj_codecontainer();
		$fnc->add_cont("function culqi() {

    if(Culqi.token) { // ¡token creado exitosamente!
        // Get the token ID:
        var token = Culqi.token.id;
        alert('Se ha creado un token:'.token);

    }else{ // ¡Hubo algún problema!
        // Mostramos JSON de objeto error en consola
        console.log(Culqi.error);
        alert(Culqi.error.mensaje);
    }
}; ;\n");
		$key=$m->getPublicKey()."";
		$fnc->add_cont("Culqi.publicKey='".$fnc->get_txt($key)."';\n");
		$params=new mwmod_mw_jsobj_obj();
		$params->set_prop("title","test");
		$params->set_prop("description","test");
		
		
		$params->set_prop("currency","PEN");
		$params->set_prop("amount",3500);
		$fnc->add_cont("Culqi.settings(".$params->get_as_js_val().");\n");
	//	$fnc->add_cont("Culqi.open();\n");
		echo $fnc->get_js_script_html();
		
		echo "<div onclick='Culqi.open()'>pagar</div>";
		
		
		
		
		$list=$culqi->Cards->getList();
		$e=$html->add_cont_elem();
		$e->add_cont(new mwmod_mw_html_cont_rawdata($list));
		
		$data=array(
		 	"card_number"=>"4444333322221111",
  			"cvv"=>"123",
  			"expiration_month"=> "09",
 			"expiration_year"=> "2020",
  			"email"=> "richard@piedpiper.com"
		);
		
		//$list=$culqi->tokens->create($data);
		//$e=$html->add_cont_elem();
		//$e->add_cont(new mwmod_mw_html_cont_rawdata($list));
		
		
		echo $html;
		
	
		
	}
	function add_req_js_scripts(){
		$jsmanager=$this->maininterface->jsmanager;
		
		$item=new mwmod_mw_html_manager_item_jsexternal("culquiapi","https://checkout.culqi.com/v2");
		$jsmanager->add_item_by_item($item);
		/*
		$jsmanager->add_item_by_cod_def_path("util.js");
		$jsmanager->add_item_by_cod_def_path("arraylist.js");
		$jsmanager->add_item_by_cod_def_path("inputs/inputs.js");
		$jsmanager->add_item_by_cod_def_path("inputs/other.js");

		$jsmanager->add_item_by_cod_def_path("inputs/container.js");
		$jsmanager->add_item_by_cod_def_path("ui/helpers/altcont.js");
		$jsmanager->add_item_by_cod_def_path("mwdevextreme/mw_dialog_helper.js");
		
		
		$item=new mwmod_mw_html_manager_item_jsexternal("ui_cart_base","/res/pastipan/cart/ui_checkoutbase.js");
		$jsmanager->add_item_by_item($item);
		$item=new mwmod_mw_html_manager_item_jsexternal("ui_cart","/res/pastipan/cart/ui_checkout.js");
		$jsmanager->add_item_by_item($item);
		*/
		
	}

}
?>