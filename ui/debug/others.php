<?php
class mwmod_mw_ui_debug_others extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Otros"));
		$this->js_ui_class_name="mw_ui_debug_test";
		
	}
	function execfrommain_getcmd_dl_tempfiletest($params=array(),$filename=false){
		return;
		ob_end_clean();
		$temp = tmpfile();
		fwrite($temp, "writing to tempfile");
		fseek($temp, 0);
		echo fread($temp, 1024);
		fclose($temp); // this removes the file
		
		
		$data=array(
			"params"=>$params,
			"filename"=>$filename,
			"uifullcod"=>$this->get_full_cod(),
			"uiclass"=>get_class($this),
			
		);
		mw_array2list_echo($data);
		return false;
	}
	function execfrommain_getcmd_dl_barcode($params=array(),$filename=false){
		$generator=new \Picqer\Barcode\BarcodeGeneratorJPG();
		$bc=$params["bc"]+0;
		$img=$generator->getBarcode($bc, $generator::TYPE_INTERLEAVED_2_5);
		//echo $img;
		ob_end_clean();
		header ('Content-type: image/jpeg');
		echo $img;
		//imagepng($img);
		//imagedestroy($img);
		/*
		$img=$generator->getBarcode($code, $generator::TYPE_INTERLEAVED_2_5);
		echo "x";
		*/
		
	}

	
	function do_exec_page_in(){
		if($sdataman=$this->uiSessionDataMan){
			//mw_array2list_echo($sdataman->get_data());
			//$sdataman->set_data(2,"n.uu.m");
		}
		//mw_array2list_echo($_SESSION);
		/*
		$sdataman=new mwmod_mw_data_session_man("xxx");
		$item=$sdataman->getItem("x");
		//$n=$item->get_data("num")+0;
		//$item->set_data($n+1,"num");
		//$item->set_data($n+1,"numx.q");
		//$item->set_data(2,"n.uu.m");
		$sitem=$sdataman->getItem("x","n");
		$sitem1=$item->getItem("n");
		$sitem2=$sitem1->getItem("z");
		$sitem2->unset_data();
		//$sitem1->set_data(1,"z.z");
		//mw_array2list_echo($_SESSION);
		mw_array2list_echo($item->get_data());
		mw_array2list_echo($sitem->get_data());
		mw_array2list_echo($sitem1->get_data());
		mw_array2list_echo($sitem2->get_data());
		*/
		
		
		return;
		$generator=new \Picqer\Barcode\BarcodeGeneratorHTML();
		$texthelper= new mwmod_mw_helper_text();
			$on=1234567891;
			$bc="2".$texthelper->fillZeros(10,$on);
			$tbc="9".$texthelper->fillZeros(12,$on);
			$bcsep=implode(",",str_split($bc,3));
			mw_array2list_echo(str_split($bc,3));
			echo "<hr>";
			echo "<div>Order: $on</div>";
			$url=$this->get_exec_cmd_dl_url("barcode",array("bc"=>$bc),"bc.jpg");
			echo "<div>Code: $bcsep <a href='$url' target='_blank'>$url</a></div>";
			echo "<div>HTML: ";
			echo  $generator->getBarcode($bc, $generator::TYPE_INTERLEAVED_2_5);
			echo "<br>IMG: <img src='$url'>";
			echo "</div>";
		for($x=0;$x<10;$x++){
			$on=$x*33;
			$bc="20".$texthelper->fillZeros(10,$on);
			$bcsep=implode("&nbsp;",str_split($bc,3));
			echo "<hr>";
			echo "<div>Order: $on</div>";
			$url=$this->get_exec_cmd_dl_url("barcode",array("bc"=>$bc),"bc.jpg");
			echo "<div>Code: $bcsep <a href='$url' target='_blank'>$url</a></div>";
			echo "<div>HTML: <br>";
			echo  $generator->getBarcode($bc, $generator::TYPE_INTERLEAVED_2_5);
			echo "<br>IMG: <img src='$url'>";
			echo "</div>";
			$bc="90".$texthelper->fillZeros(10,$on);
			$bcsep=implode("&nbsp;",str_split($bc,3));
			
			$url=$this->get_exec_cmd_dl_url("barcode",array("bc"=>$bc),"bc.jpg");
			echo "<div>TEST Code: $bcsep <a href='$url' target='_blank'>$url</a></div>";
			echo "<div>HTML: <br>";
			echo  $generator->getBarcode($bc, $generator::TYPE_INTERLEAVED_2_5);
			echo "<br>IMG: <img src='$url'>";
			echo "</div>";
			
			
			
			
		}
		
		/*
		
	
		echo  $generator->getBarcode('30000000005', $generator::TYPE_INTERLEAVED_2_5);
		echo  $generator->getBarcode('34511234565', $generator::TYPE_INTERLEAVED_2_5);
		echo  $generator->getBarcode('A1234565', $generator::TYPE_STANDARD_2_5);
		*/
		return;
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No hay manejador de usuarios.")."</p>";	
			return false;	
		}
		$permissions=false;
		if($permissions_man=$uman->get_permission_man()){
			if($permissions=$permissions_man->get_items()){
				$d=array();
				foreach($permissions as $id=>$permission){	
					$d[$id]=$permission->get_debug_data();
					$d[$id]["allowed"]=$permission->allowed();
					$d[$id]["allowed_info"]=$permission->allow_debug_info;
					
				}
				mw_array2list_echo($d,"permisos");
			}
		}
		
		
		
		return;
		echo pow (2 , 3 )."<br>";
		echo pow (2 , 2 )."<br>";
		$tt=array();
		$refdate=new mwmod_mw_date_date("2015-3-8 7:7:9");
		for($x=1;$x<13;$x++){
			$date=new mwmod_mw_date_date("2015-".$x."-8 8:7:9");	
			$tt[$x]["actual"]=$date->get_diff_debug_data($refdate);
			$tt[$x]["ref"]=$refdate->get_diff_debug_data($date);
			//$tt[$x]=$date->get_debug_data();
		}
		//$date=new mwmod_mw_date_date("2015-05-8 8:7:9");
		mw_array2list_echo($tt);
		return;
		$cellutil= new mwmod_mw_phpoffice_cellindexutil(1);
		for($x=0;$x<10;$x++){
			echo $cellutil->getColCoordinate()." ".$cellutil->currentCol."<br>";
			$cellutil->nextCol();
		}
		
		
		
		$container=$this->get_ui_dom_elem_container();
		$e=$this->set_ui_dom_elem_id("datepicker");
		$container->add_cont($e);
		/*
		$btn=$this->set_ui_dom_elem_id("btnno");
		$btn->add_cont("No mwap");
		$container->add_cont($btn);
		$btn=$this->set_ui_dom_elem_id("btn");
		$btn->add_cont("mwap");
		$container->add_cont($btn);
		*/
		
		$jsui=$this->new_ui_js();
		$this->set_ui_js_params();
		//$this->ui_js_init_params->set_prop("sorttest",$list);
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		echo $js->get_js_script_html();
		
		echo $js->get_as_js_val();
		
		
		return;
		
		$list=new mwmod_mw_jsobj_array();
		
		$time=time();
		$names=explode(",","Jhon,Alberto,niña,sandra,Jhon");
		$x=0;
		foreach($names as $n){
			$t=strtotime(date("Y-m-d H:i:s",$time)." + $x months");
			$obj=new mwmod_mw_jsobj_obj();
			$obj->set_prop("cod",$x+1);
			$obj->set_prop("name",$n);
			$jsd=new mwmod_mw_jsobj_date($t);
			$obj->set_prop("time",$jsd);
			$jsd=new mwmod_mw_jsobj_date(date("Y-m-d H:i:s",$t));
			$obj->set_prop("date",$jsd);
			$jsd=new mwmod_mw_jsobj_date(date("Y-m-d H:i:s",$t));
			$obj->set_prop("datenohour",$jsd);
			$jsd->include_hour=false;
			$list->add_data($obj);
			
			
			$x++;
		}
		$container=$this->get_ui_dom_elem_container();
		/*
		$btn=$this->set_ui_dom_elem_id("btnno");
		$btn->add_cont("No mwap");
		$container->add_cont($btn);
		$btn=$this->set_ui_dom_elem_id("btn");
		$btn->add_cont("mwap");
		$container->add_cont($btn);
		*/
		
		$jsui=$this->new_ui_js();
		$this->set_ui_js_params();
		$this->ui_js_init_params->set_prop("sorttest",$list);
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		echo $js->get_js_script_html();
		
		echo $js->get_as_js_val();
		
		/*
		$str="[
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	]";
	$str = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $str); // fix variable names 
	var_dump(json_decode($str));
	echo "<br>";
		$str="{aaa:'eer',b:3,list:[
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	]}";
	$str = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $str); // fix variable names 
		var_dump(json_decode($str));
	echo "<br>";

		$str="{aaa:'eer','b':3}";
		$str = preg_replace("/([a-zA-Z0-9_]+?):/" , "\"$1\":", $str); // fix variable names 
		var_dump(json_decode($str));
	echo "<br>";
		$str = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
		
		var_dump(json_decode($str));
	echo "<br>";
	*/
		 $man=mw_get_autoload_manager();
		 
		 $subman=$man->get_ns_pref_man_for_class("PhpOffice\PhpWord\PhpWord");
		 echo $subman->get_class_path_full("PhpOffice\PhpWord\PhpWord")."<br>";
		 
		 $PHPExcel=new PhpOffice\PhpWord\PhpWord();
		echo get_class($PHPExcel)."<br>";
		
		
		 $subman=$man->get_ns_pref_man_for_class("PhpOffice\PhpWord\Metadata\DocInfo");
		 echo $subman->get_class_path_full("PhpOffice\PhpWord\Metadata\DocInfo")."<br>";
		 $PHPExcel=new PhpOffice\PhpWord\Metadata\DocInfo();
		echo get_class($PHPExcel)."<br>";
		
		
		return;
		
		
		 $man=mw_get_autoload_manager();
		 
		 $subman=$man->get_pref_man("PHPExcel");
		 $subman->before_autoload_first();
		 $subman->before_autoload_first();
		 
		 echo $subman->get_class_path_full("PHPExcel_Style_Border")."<br>";
		 echo $subman->get_class_path_full("PHPExcel_Autoloader")."<br>";
		 echo $subman->get_class_path_full("PHPExcel")."<br>";
		 
		 $PHPExcel=new PHPExcel();
		echo get_class($PHPExcel);
		if (ini_get('mbstring.func_overload') & 2) {
			echo "ff";
		}
		echo "xx";
		return;
		
		$this->do_exec_page_in_kcfinder();
		return;
		$this->do_exec_page_in_replacement();
		return;
		
		$helper= new mwmod_mw_ap_helper();
		$date=$helper->dateman->new_date("2015-10-27 7:55");
		mw_array2list_echo($date->get_debug_data());
		
		
		return;
		$this->do_exec_page_in_kcfinder();
		return;
		
		
		$this->do_exec_page_in_js();
		return;
		if($su=$this->maininterface->get_sub_interface_by_dot_cod("tasks.selflow.view")){
			echo get_class($su)."<br>";	
			
		}
		if($su=$this->maininterface->get_sub_interface_by_dot_cod("tasks")){
			echo get_class($su)."<br>";	
			if($su1=$su->get_sub_interface_by_dot_cod("selflow")){
				echo get_class($su1)."<br>";	
			}
			if($su1=$su->get_sub_interface_by_dot_cod("selflow.view")){
				echo get_class($su1)."<br>";	
			}
		}
		
		
		//return $this->do_exec_page_in_tasks();
		
		return ;
		
		$helper= new mwmod_mw_ap_helper();
		$exts=$helper->file_man->get_allowed_exts();
		$not_existing=array();
		$types_ok=$helper->mimetypes->get_mime_types_list($exts,$not_existing,false);
		mw_array2list_echo($not_existing,"not_existing");
		$types_2_add=$helper->mimetypes->get_mime_types_list($not_existing);
		
		//$mimes=$helper->mimetypes->get_missing_on_full();
		$mimes=$helper->mimetypes->get_mime_types();
		//mw_array2list_echo($not_existing,"falta");
		mw_array2list_echo($mimes,"ok");
		
		//mw_array2list_echo($helper->mimetypes->get_mime_types());
	  
		  echo "<textarea>";
		  foreach($mimes as $ext=>$m){
				echo "\"".$ext."\"=>\"".$m."\",\n";  
		  }
		  
		  /*
		  foreach($mimes as $ext=>$m){
				echo "\"".$ext."\"=>\"".$m."\",\n";  
		  }
		  */
		  echo "</textarea>";
		
		/*
		$on_fp="7z,aiff,ani,asf,avi,bmp,class,css,csv,cur,dae,dat,doc,docx,fla,flv,gif,gz,gzip,html,ico,jar,jpeg,jpg,js,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pat,pdf,png,pps,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,wrl,wrm,wrml,xls,xlsx,xml,zip
";
		$on_cfg="avi,doc,docx,gif,jpg,mp3,mp4,pdf,png,ppt,pptx,rtf,swf,txt,xls,xlsx";
		$list=explode(",",$on_fp.",".$on_cfg);
		$full_list=array();
		foreach($list as $cod){
			foreach($list as $cod){
				if(!$full_list[$cod]){
					$full_list[$cod]=$cod;	
				}
			}
				
		}
		reset($full_list);
		ksort($full_list);
		echo implode(",",$full_list);
		
		*/
		

		
		
		return
		$helper= new mwmod_mw_ap_helper();
		$exts=$helper->file_man->get_allowed_exts();
		$not_existing=array();
		
		
		$for_short="7z,aiff,ani,asf,avi,bmp,class,css,csv,cur,dae,dat,doc,docx,fla,flv,gif,gz,gzip,html,ico,jar,jpeg,jpg,js,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pat,pdf,png,pps,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,wrl,wrm,wrml,xls,xlsx,xml,zip
";
		$for_short.=",rar,rmi,rmvb,sdc,sitd,sxc,sxw,tgz,vsd,wma,wmv,wrm,wrml";
		$for_short.=",au,avi,bmp,bz2,css,dtd,doc,docx,dotx,es,exe,gif,gz,hqx,html,jar,jpg,js,midi,mp3,mpeg,ogg,pdf";
		
		$for_short.=",pl,png,potx,ppsx,ppt,pptx,ps,qt,ra,ram,rdf,rtf,sgml,sit,sldx,svg,swf,targz,tgz,tiff";
		$for_short.=",tsv,txt,wav,xlam,xls,xlsb,xlsx,xltx,xml,zip";
		$for_short.="";
		
		
		
		$types_ok=$helper->mimetypes->get_mime_types_list(",tsv,txt,wav,xlam,xls,xlsb,xlsx,xltx,xml,zip",$not_existing);
		
		
		/*
		au,avi,bmp,bz2,css,dtd,doc,docx,dotx,es,exe,gif,gz,hqx,html,jar,jpg,js,midi,mp3,mpeg,ogg,pdf,pl,png,potx,ppsx,ppt,,pptx,ps,qt,ra,ram,rdf,rtf,sgml,sit,sldx,svg,swf,targz,tgz,tiff,tsv,txt,wav,xlam,xls,xlsb,xlsx,xltx,xml,zip
		*/

		/*
		$full_list=array();
		$pathman=$this->mainap->get_path_man("system");
		$file=$pathman->get_file_path_if_exists("web1.txt","data/mimetypes/temp");
		if($list=$helper->mimetypes->get_mime_types_from_file($file)){
			//mw_array2list_echo($list);
			foreach($list as $cod=>$t){
				if(!$full_list[$cod]){
					$full_list[$cod]=$t;	
				}
			}
		}
		$file=$pathman->get_file_path_if_exists("php.txt","data/mimetypes/temp");
		if($list=$helper->mimetypes->get_mime_types_from_file($file)){
			//mw_array2list_echo($list);
			foreach($list as $cod=>$t){
				if(!$full_list[$cod]){
					$full_list[$cod]=$t;	
				}
			}
		}
		$file=$pathman->get_file_path_if_exists("web2.txt","data/mimetypes/temp");
		if($list=$helper->mimetypes->get_mime_types_from_file($file)){
			//mw_array2list_echo($list);
			foreach($list as $cod=>$t){
				if(!$full_list[$cod]){
					$full_list[$cod]=$t;	
				}
			}
		}
		reset($full_list);
		ksort($full_list);
		 echo "<textarea>";
		 foreach($full_list as $ext=>$m){
			echo "".$ext." ".$m."\n";  
		 }
		  
		echo "</textarea>";
		*/
		
		
		return;
		
		
		
		
		
		
		
		$exts=$helper->file_man->get_allowed_exts();
		$not_existing=array();
		$types_ok=$helper->mimetypes->get_mime_types_list("7z,ani,asf,csv,cur,dae,dat,fla,flv,gz,gzip,ico,jar,mpc,ods,odt,pat,pps,pxd,rar,rmi,rmvb,sdc,sitd,sxc,sxw,tgz,vsd,wma,wmv,wrm,wrml",$not_existing);
		
		//$mimes=$helper->mimetypes->get_missing_on_full();
		//$mimes=$helper->mimetypes->get_mime_types();
		//mw_array2list_echo($not_existing,"falta");
		//mw_array2list_echo($types_ok,"ok");
		
		//mw_array2list_echo($helper->mimetypes->get_mime_types());
	  
		  echo "<textarea>";
		  foreach($not_existing as $ext=>$m){
				echo "".$ext." ".$m."\n";  
		  }
		  
		  /*
		  foreach($mimes as $ext=>$m){
				echo "\"".$ext."\"=>\"".$m."\",\n";  
		  }
		  */
		  echo "</textarea>";
		  
	  

		
		return;
		
		return $this->do_exec_page_in_js();
		return $this->do_exec_page_in_tasks();
		
		//return $this->do_exec_page_in_get_priv_props();
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			return false;	
		}
		
		//$jsfull=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$jsui=$this->new_ui_js();
		$jsui->set_fnc_name("mw_ui_grid");
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagriditems");
		$datagrid->dont_rewrite_dataSourceProp=true;
		$datagrid->dont_rewrite_columnsProp=true;
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		//$jsbefore->add_cont("test();\n");
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		$jsbefore->add_cont("ui.set_container('uidebugelem');\n");
		echo "<div id='uidebugelem'></div>";
		
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",false);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);	

		//$datagrid->js_props->set_prop("paging.enabled",false);	
		$datagrid->js_props->set_prop("editing.editMode","row");	
		$datagrid->js_props->set_prop("editing.editEnabled",true);	
		$datagrid->js_props->set_prop("editing.removeEnabled",false);	
		$datagrid->js_props->set_prop("editing.insertEnabled",true);
		

		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("group","Grupo"));
		$col=$datagrid->add_column_boolean("active",$this->lng_common_get_msg_txt("active","Activo"));
		$col->js_data->set_prop("filterValue",true);

		$col=$datagrid->add_column_date("date","Fecha");
		
		$dataoptim=$datagrid->new_dataoptim_data_man();
		/*
		$f=$dataoptim->add_data_field("id");
		$f->numeric_mode();
		$f=$dataoptim->add_data_field("name");
		$f=$dataoptim->add_data_field("active");
		*/
		$testdata=$this->get_test_data();
		//mw_array2list_echo($testdata);
		
		
		
		if($items=$man->get_all_items()){
			foreach($items as $cod=>$item){
				$datagrid->add_data($item->get_data());
				$dataoptim->add_data($item->get_data());
					
				
			}
		}
		foreach($testdata as $t){
			$dataoptim->add_data($t);	
		}
		$datagrid->set_js_ui_events("rowRemoved,rowUpdating,rowInserting,rowInserted,initNewRow");
		
		//$datagrid->htmlelem->set_style("width","400px");
		//$datagrid->htmlelem->set_style("margin","auto");
		echo $datagrid->get_html_container();
		
		
		
		$js=$datagrid->new_js_doc_ready();
		$datajs=$datagrid->get_data_as_js_array();
		$jsbefore->add_cont("var dsoptim=".$dataoptim->get_as_js_val().";\n");
		$jsbefore->add_cont("var ds=dsoptim.get_all_data();\n");
		
		$jsbefore->add_cont("var helper=new mw_devextreme_datagrid_man();\n");
		$columns=$datagrid->columns->get_items();
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$jsbefore->add_cont("helper.add_colum(".$coljs->get_as_js_val().");\n");	
		}

		
		//$jsbefore->add_cont("var ds=".$datajs->get_as_js_val().";\n");
		$datagrid->dont_rewrite_dataSourceProp=true;
		$datagrid->js_props->set_prop("dataSource",new mwmod_mw_jsobj_code("ds"));	
		$datagrid->js_props->set_prop("columns",new mwmod_mw_jsobj_code("helper.get_columns_options()"));	
		$js->add_cont($jsbefore,true);
		$js->add_cont("ui.set_grid('".$datagrid->container_id."')");

		//$datagrid->prepare_js_props();
		echo nl2br($js->get_as_js_val());

		echo $js->get_js_script_html();

		

		
		
		

		
		
		/*
		$imgverman=$this->mainap->get_submanager("captcha");
		$item=$imgverman->new_item_for_input("test");
		$url=$item->get_url();
		echo "<a href='$url' target='_blank'>$url</a>";
		$this->do_exec_page_in_sql();
		return;
		
		*/
		
	}
	function do_exec_page_in_replacement(){
		
		echo '<button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Tooltip on left">Tooltip on left</button>';
		$taskman=$this->mainap->get_submanager("tasks");
		
		$js=$taskman->get_users_avaible_to_receive_tasks_and_replacements_js();
		echo nl2br($js->get_as_js_val());
		
		
		
		
		$data=array();
		$uman=$this->mainap->get_user_manager();
		$users=$uman->get_all_useres();
		foreach($users as $id=>$user){
			$u=new mwmod_mw_users_util_replacement($user);
			$u->permition_cod="workflow";
			$data[$id]["orig"]=$user->get_real_name();	
			if($other=$taskman->get_user_avaible_to_receive_tasks_or_replacement($id)){
				$data[$id]["final"]=$other->get_real_name();		
			}else{
				$data[$id]["final"]="none";	
			}
			$data[$id]["r_util"]=$u->get_debug_data();	
		}
		mw_array2list_echo($data);
		
			
	}
	
	function do_exec_page_in_kcfinder(){
		$kcfinder_man=$this->mainap->get_submanager("kcfinder");
		/*
		$p=array();
		$c=array();
		$setter=new mwmod_mw_kcfinder_cfgsetter($p,$c);
		$setter->set_upload_url_by_rel("debug/kcfinder");
		mw_array2list_echo($c);
		*/
		
		$container=$this->get_ui_dom_elem_container();
		$btn=$this->set_ui_dom_elem_id("btnno");
		$btn->add_cont("No mwap");
		$container->add_cont($btn);
		$btn=$this->set_ui_dom_elem_id("btn");
		$btn->add_cont("mwap");
		$container->add_cont($btn);
		
		
		$jsui=$this->new_ui_js();
		$this->set_ui_js_params();
		$ckeditorcfg=$kcfinder_man->set_ckeditor_js_cfg("kcfinder","debug");
		$this->ui_js_init_params->set_prop("ckeditorcfg",$ckeditorcfg);
		$this->ui_js_init_params->set_prop("kcfinderurl",$kcfinder_man->get_url("kcfinder","debug",array("xx"=>"q")));
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		echo $js->get_js_script_html();
		
			
	}

	function do_exec_page_in_js(){
		
		echo "<div id='myContextMenu'></div>";
		$container=$this->get_ui_dom_elem_container();
		$jsui=$this->new_ui_js();
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		echo $js->get_js_script_html();

		/*
		echo "<div id='xxx' style='border:#ff0000 solid 1px'>xxx</div>";
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("$( '#xxx' ).resizable({ handles: 'n, e, s, w'});");
		//$js->add_cont("alert('dd');");
		echo $js->get_js_script_html();
		*/
		/*
		if(strtotime()){
			
		}
		*/
		/*
		
		if(!$tasksman=$this->mainap->get_submanager("tasks")){
			echo "no man";	
			return;
		}
		
		//$jobsman=$tasksman->jobs_man;
		
		//mw_array2list_echo($jobsman->get_debug_data());
		$man=$tasksman->daily_log_man;
		for($x=2;$x<45;$x++){
			$d=strtotime(date("Y-m-d")." - $x days");
			echo date("Y-m-d",$d)."<br>";
			//$man->update_day($d);
		}
		*/
		//$man->do_daily_log();
		
		//$man->update_day(strtotime("2015-04-15"));
		//$data=$man->get_data_for_day(strtotime("2015-04-15"));
		//mw_array2list_echo($data);
	}

	function do_exec_page_in_jobs(){
		if(!$man=$this->mainap->get_submanager("jobsdebug")){
			echo "no man";	
			return;
		}
		$man->debug();
	}
	
	
	function do_exec_page_in_get_priv_props(){
		
		
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		if($_REQUEST["testdata"]){
			mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="testdata";
		$input=$cr->add_item(new mwmod_mw_datafield_textarea("privvarnames","Propiedades privadas"));
		$submit=$cr->add_submit("Enviar");
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();
	
		
	}
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		/*
		$util=new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->add_jquery_ui();
		$util->add_js_item_by_cod_def_path("url.js");
		$util->add_js_item_by_cod_def_path("ajax.js");
		$util->add_js_item_by_cod_def_path("mw_objcol.js");
		$util->add_js_item_by_cod_def_path("ui/mwui.js");
		$util->add_js_item_by_cod_def_path("ui/mwui_grid.js");
		
		$util->add_js_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		*/
		$item=new mwmod_mw_html_manager_item_jsexternal("mw_test_ui","/res/debug/mw_test_ui.js");
		$util->add_js_item($item);
		$item=new mwmod_mw_html_manager_item_jsexternal("ckeditor","/res/ckeditor/ckeditor.js");
		$util->add_js_item($item);
		
		
		/*


		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		*/
		
	}
	function get_test_data(){
		$r=array();
		$active=1;
		for($x=100;$x<200;$x++){
			$date=date("Y-m-d H:i:s",strtotime("2015-02-15 4:5:1 + $x days"));
			if($active){
				$active=0;
				$d=array(
					"id"=>$x,
					"active"=>$active,
					"date"=>$date,
					"name"=>"Elemento $x",
					
				);	
			}else{
				$active=1;
				$d=array(
					"name"=>"Elemento $x",
					"active"=>$active,
					"date"=>$date,
					"id"=>$x,
					
				);	
					
			}
			$r[]=$d;
		}
		return $r;
	}
	
	function do_exec_page_in_sql(){
		$uman=$this->mainap->get_user_manager();
		$passpolicy=$uman->get_pass_policy();
		echo $passpolicy->new_random();
		//echo get_class($uman);

		//$tblman=$uman->tblman;
		//echo get_class($tblman);
		//$query=$uman->tblman->new_query();
		//$query=$uman->new_users_query();
		/*
		if(!$query=$uman->get_users_with_mail("Rodrigo@nosabemosnada.com")){
			echo "no";
			return false;	
		}
		*/
		
		
		
		
		//echo $query->get_sql();
		//mw_array2list_echo($query->get_array_data_from_sql());	
		
		
		
		//$query=new mwmod_mw_db_sql_query("users");
		//$query->
	}

	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>