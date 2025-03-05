<?php
class mwmod_mw_ui_debug_util_privprops extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Propiedades privadas");
		
	}
	function get_full_result($input){
		$r=array();
		if(!is_array($input)){
			return $r;	
		}
		$r["options"]=$input;
		if(!$varnames=$this->get_varnames($input["privvarnames"])){
			return $r;	
		}
		$r["options"]["privvarnames"]=implode("\n",$varnames);
		$r["result"]=$this->get_result($varnames);
		return $r;	
		
	}
	function get_result($varnames){
		$rr=array();
		if(!$r=$this->get_result_as_array($varnames)){
			return $rr;	
		}
		//$input=$result->add_item(new mwmod_mw_datafield_textarea("declaration","Declaración"));
		//$input=$result->add_item(new mwmod_mw_datafield_textarea("getmethods","Métodos get"));
		//$input=$result->add_item(new mwmod_mw_datafield_textarea("creationmethos","Métodos creacion"));
		$declaration=array();
		$getmethods=array();
		$creationmethos=array();
		foreach($r as $varname=>$d){
			$declaration[]=$d["declaration"];
			$getmethods[]=$d["get_method_simple"];
			$creationmethos[]=$d["create_method"].$d["get_method_with_create"];
		}
		$rr["declaration"]=implode("\n",$declaration);
		$rr["getmethods"]=implode("\n",$getmethods);
		$rr["creationmethos"]=implode("\n\n",$creationmethos);
		
		
		
	//	mw_array2list_echo($r);
		return $rr;	
		
	}
	function get_result_for_var($varname){
		if(!$varname=trim($varname)){
			return false;	
		}
		$r=array();
		$r["varname"]=$varname;
		$r["declaration"]="\t".'private $'.$varname.";";
		$get_method="__get_priv_".$varname;
		$r["get_method_name"]=$get_method;
		$create_method="create_".$varname;
		$r["create_method_name"]=$create_method;
		$r["get_method_simple"]="\tfinal function ".$get_method."(){\n";
		$r["get_method_simple"].="\t\treturn ".'$this->'.$varname.";\n";
		$r["get_method_simple"].="\t}\n";

		$r["create_method"]="\tfunction ".$create_method."(){\n";
		$r["create_method"].="\t\t".'$a'."=1;\n";
		$r["create_method"].="\t\treturn ".'$a'.";\n";
		$r["create_method"].="\t}\n";
		
		$r["get_method_with_create"]="\tfinal function ".$get_method."(){\n";
		$r["get_method_with_create"].="\t\tif(!isset(".'$this->'.$varname.")){\n";
		$r["get_method_with_create"].="\t\t\t".'$this->'.$varname."=".'$this->'."$create_method();\n";
		$r["get_method_with_create"].="\t\t}\n";
		$r["get_method_with_create"].="\t\treturn ".'$this->'.$varname.";\n";
		$r["get_method_with_create"].="\t}\n";
		return $r;
		
		
		
		
		
	}
	
	function get_result_as_array($varnames){
		if(!is_array($varnames)){
			return false;	
		}
		$r=array();
		foreach($varnames as $varname){
			if($rr=$this->get_result_for_var($varname)){
				$r[$varname]=$rr;	
			}
		}
		return $r;
	}
	
	function get_varname_from_line($line){
		if(!$line=trim($line)){
			return false;	
		}
		$pos=strpos($line,'$');
		if($pos!==false){
			$line=substr($line,$pos+1);	
		}
		$pos=strpos($line,'=');
		if($pos!==false){
			$line=substr($line,0,$pos);	
		}
		$pos=strpos($line,';');
		if($pos!==false){
			$line=substr($line,0,$pos);	
		}
		return trim($line);
		
				
		
	}
	
	
	function get_varnames($input){
		if(!$input){
			return false;	
		}
		$vars=explode("\n",$input."");
		$r=array();
		foreach($vars as $line){
			if($v=$this->get_varname_from_line($line)){
				$r[$v]=$v;	
			}
		}
		return $r;
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$gr=$cr->add_item(new mwmod_mw_datafield_group("options"));
		$input=$gr->add_item(new mwmod_mw_datafield_textarea("privvarnames","Propiedades privadas"));
		//$input=$gr->add_item(new mwmod_mw_datafield_checkbox("create","Creación"));
		$result=$cr->add_item(new mwmod_mw_datafield_groupwithtitle("result","Resultado"));
		$input=$result->add_item(new mwmod_mw_datafield_textarea("declaration","Declaración"));
		$input=$result->add_item(new mwmod_mw_datafield_textarea("getmethods","Métodos get"));
		$input=$result->add_item(new mwmod_mw_datafield_textarea("creationmethos","Métodos creacion"));
		
		if($_REQUEST["data"]??null){
			//mw_array2list_echo($_REQUEST["data"]);
			if(is_array($_REQUEST["data"]["options"]??null)){
				$cr->set_data($this->get_full_result($_REQUEST["data"]["options"]));		
			}
		}
		$cr->items_pref="data";
		
		$submit=$cr->add_submit("Enviar");
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();
	
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>