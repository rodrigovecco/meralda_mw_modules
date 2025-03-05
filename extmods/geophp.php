<?php
$subpathman=mw_get_main_ap()->get_sub_path_man("modulesext/geophp","system");
if($fullpath=$subpathman->get_file_path_if_exists("geoPHP.inc")){
	require_once $fullpath;
}
class mwmod_mw_extmods_geophp extends mw_baseobj{
	function moduleInstalled(){
        if(class_exists("geoPHP",false)){
            return true;
        }else{
            return false;
        }
    }
}

?>