<?php
class mwmod_mw_mnu_mnuitem extends mw_apsubbaseobj{
	public $img_url;
	public $allowed=true;
	
	private $items=array();
	private $subhtmlelems=array();
	private $parent;
	public $mnu;
	public $params=array();
	public $cod;
	public $innerHTMLbefore="";
	public $innerHTMLafter="";
	
	private $_template_output_cod=false;
	
	public $active=false;
	public $is_dropdown=false;
	public $bt_icon;
	public $tooltip;
	public $elemsIDpref="MNU";
	public $url;
	public $etq;

	public $isEnd;//last item available

	
	
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
	}
	function addToHtmlTopNavULSbadmin($navUL,$level=0){
		//20231022 extender si hay necesidad
		//cambiar acá 
		if($e=$this->get_html_as_navlist_item()){
			$navUL->add_cont($e);
		}

	}
	function addToHtmlAsDropDownChild($navUL,$level=0){
		$li=new mwmod_mw_html_elem("li");
		$navUL->add_cont($li);
		
		$a=$li->add_cont_elem("","a");
		$a->addClass("dropdown-item");
		if($str=$this->get_url()){
			$a->set_att("href",$str);
		}else{
			$a->set_att("href","#");
		}
			
		if($str=$this->get_target()){
			$a->set_att("target",$str);
		}
		$e=$a->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());//revisar iconos


	
	}
	function addToHtmlSideNav($navDIV,$level=0){
		$subItems=$this->get_items_allowed();

		$a=new mwmod_mw_html_elem("a");
		$navDIV->add_cont($a);
		if($str=$this->get_url()){
			$a->set_att("href",$str);
		}else{
			$a->set_att("href","#");
		}
		$a->addClass("nav-link");
		$id=$this->getElemID("dd");
		if($str=$this->get_target()){
			$a->set_att("target",$str);
		}
		$e=$a->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());
		if($this->active){
			//$e->add_cont("AAA");
		}

		if($subItems){
			$a->set_att("data-bs-toggle","collapse");
			$a->set_att("data-bs-target","#$id");
			$a->set_att("aria-expanded","true");
			$a->set_att("aria-controls","$id");
			//$a->addClass("collapsed");
			if(!$this->active){
				$a->addClass("collapsed");
			}
			$arrow=$a->add_cont_elem();
			$arrow->addClass("sb-sidenav-collapse-arrow");
			$arrow->add_cont('<i class="fas fa-angle-down"></i>');
			

			
			$subItemsDiv=new mwmod_mw_html_elem("div");
			if(!$this->active){
				$subItemsDiv->addClass("collapse");
			}
			$subItemsDiv->set_att("id","$id");
			$navDIV->add_cont($subItemsDiv);
			$subItemsNav=$subItemsDiv->add_cont_elem("","nav");
			$subItemsNav->addClass("sb-sidenav-menu-nested nav");
			foreach($subItems as $subItem){
				$subItem->addToHtmlSideNav($subItemsNav,$level+1);
			}

	
		}
	
	}
	







	function getElemID($cod=""){
		$c=$this->cod;
		if($cod){
			$c.="-".$cod;	
		}
		if($this->parent){
			return $this->parent->getElemID($c);
		}elseif($this->mnu){
			return $this->mnu->getElemID($c);
		}
		return $this->elemsIDpref."-".$c;
	}
	final function getSubHTMLElem($cod,$add=false){
		

		if($this->subhtmlelems[$cod]??null){
			return $this->subhtmlelems[$cod];	
		}
		if(!$add){
			return false;	
		}
		if(is_object($add)){
			$this->subhtmlelems[$cod]=$add;
			return $add;	
		}else{
			$this->subhtmlelems[$cod]= new mwmod_mw_html_cont_varcont(); 
			return $this->subhtmlelems[$cod];	
		}
	}
	function get_li_class_name(){
		if($this->is_active()){
			return "active";	
		}
	}
	function get_html_as_nav_child_inner(){
		if($e=$this->get_navlist_link_elem()){
			return $e->get_as_html();	
		}
		$r.=$this->get_alink();
		//$r.=$this->get_html_children();
		return $r;
			
	}
	
	function get_html_as_nav_child(){
		$class=$this->get_li_class_name();
		$r="";
		if($class){
			$r.="<li class='$class nav-item'>\n";	
		}else{
			$r.="<li class='nav-item'>\n";	
		}
		$r.=$this->get_html_as_nav_child_inner();
		$r.="</li>\n";	
		return $r;
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["lbl"]=$this->get_etq();
		$r["url"]=$this->get_url();
		
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach ($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();
			}
		}
		return $r;
		
			
	}
	
	function set_dropdown($val=true){
		if($val){
			$this->is_dropdown=true;	
		}else{
			$this->is_dropdown=false;	
		}
			
	}
	function is_dropdown(){
		return $this->is_dropdown;	
	}

	function is_active(){
		return $this->active;	
	}
	function set_active($val=true){
		if($val){
			$this->active=true;	
		}else{
			$this->active=false;	
		}
	}
	function set_active_by_url(){
		if($_SERVER[REQUEST_URI]==$this->get_url()){
			$this->set_active(true);	
		}
	}
	function addInnerHTML_icon($class,$before=true){
		$h="<i class='$class'></i> ";
		if($before){
			$this->innerHTMLbefore.=$h;	
		}else{
			$this->innerHTMLafter.=$h;	
		}
	}
	function set_param($cod,$val){
		$this->params[$cod]=$val;	
	}
	function add_param($cod,$val,$sep=" "){
		if(strlen($this->params[$cod]??"")){
			$this->params[$cod].=$sep.$val;	
		}else{
			$this->params[$cod]=$val;	
		}
	}
	function get_param($cod){
		if(isset($this->params[$cod])){
			return $this->params[$cod];
		}
		
	}
	function getParam($cod,$def=false){
		if($this->paramExists($cod)){
			return $this->get_param($cod);	
		}
		return $def;
		
	}
	function paramExists($cod){
		if(isset($this->params[$cod])){
			return true;
		}
		return false;
	}
	
	final function get_template_output_cod(){
		return $this->_template_output_cod;	
	}
	final function set_template_output_cod($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			$cod=false;
		}
		$this->_template_output_cod=$cod;
		return $cod;
	}
	function get_html_as_collapse_item(){
		$c=new mwmod_mw_html_elem("a");
		if($str=$this->get_url()){
			$c->set_att("href",$str);
		}else{
			$c->set_att("href","#");
		}
		$c->addClass("collapse-item");
		if($str=$this->get_target()){
			$c->set_att("target",$str);
		}
		$e=$c->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());
		return $c;
			
	}
	final function set_parent($parent){
		$this->parent=$parent;
	}
	final function get_parent(){
		return $this->parent;
	}
	function get_allowed_items_num(){
		if(!$items=$this->get_items_allowed()){
			return 0;	
		}
		return sizeof($items);
			
	}
	final function get_items(){
		return $this->items;
	}
	function get_items_allowed(){
		$r=array();
		if(!$items=$this->get_items()){
			return false;	
		}
		$endItem=false;
		foreach ($items as $cod=>$item){
			$item->isEnd=false;
			if($item->is_allowed()){
				$r[$cod]=$item;
				$endItem=$item;
			}
		}
		if($endItem){
			$endItem->isEnd=true;
		}
		if(sizeof($r)<1){
			return false;
		}
		return $r;
	}
	function add_new_item($cod,$etq,$url=false){
		if($i=$this->create_item($cod,$etq,$url)){
			return $this->add_item($cod,$i);	
		}
	}
	
	function add_item_by_item($item){
		return $this->add_item($item->cod,$item);
	}
	
	final function add_item($cod,$item){
		if(!is_array($this->items)){
			$this->items=array();	
		}
		$this->items[$cod]=$item;
		$item->on_add($this);
		
		return $this->items[$cod];
	}
	function create_item($cod,$etq,$url=false){
		$i=	new mwmod_mw_mnu_mnuitem($cod,$etq,$this,$url);
		return $i;
	}

	
	
	
	function is_allowed(){
		return $this->allowed;	
	}
	function set_img_url($url){
		$this->img_url=$url;
	}
	function get_img_url(){
		return $this->img_url;
	}

	function get_html_open(){
		return "<span>";	
	}
	function get_html_close(){
		return "</span>";	
	}
	function get_html_for_template($template){
		if($template->allow_get_html_by_outputcod_item($this)){
			return $template->get_html_item_by_outputcod($this);	
		}else{
			return $this->get_html();	
		}
	}
	
	function get_html_as_dropdown_child(){
		$r=$this->get_alink();
		//$r.=$this->get_html_children();
		return $r;
			
	}
	function get_html_as_list_inner(){
		$r=$this->get_alink();
		$r.=$this->get_html_children();
		return $r;
			
	}
	function get_li_class_list(){
		$r=array();
		
	
		if($this->is_dropdown()){
			$r[]="dropdown";	
		}
		if($this->is_active()){
			$r[]="active";	
		}
		
		if(sizeof($r)){
			return $r;	
		}
	}
	function get_navli_class_list(){
		$r=array();
		
		$r[]="nav-item";	
	
		if($this->is_dropdown()){
			$r[]="dropdown";	
		}
		if($this->is_active()){
			$r[]="active";	
		}
		
		if(sizeof($r)){
			return $r;	
		}
	}
	function get_navli_class(){
		if(!$list=$this->get_navli_class_list()){
			return false;	
		}
		return implode(" ",$list);
	}
	function get_html_as_navli_open(){
		if($c=$this->get_navli_class()){
			return "<li class='$c'>";	
		}
		return "<li>";	
	}
	function get_html_as_navli_close(){
		return "</li>";	
	}
	function get_html_as_navlist_item(){
		$r=$this->get_html_as_navli_open();
		$r.=$this->get_html_as_navlist_inner();
		//$r.=$this->get_html_children();
		$r.=$this->get_html_as_navli_close();
		return $r;
			
	}
	function get_html_as_navlist_inner(){
		$r="";
		if($e=$this->get_navlist_link_elem()){
			$r.=$e->get_as_html();	
		}
		$r.=$this->get_html_children_nav();
		return $r;
			
	}
	function get_html_children_nav(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		if($this->is_dropdown()){
			//$r.="<a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false'><span class='caret'></span></a>";
			//$r="<ul class='dropdown-menu mw-dropdown-menu' role='menu'>";
			$r="<ul class='dropdown-menu'>";
				
		}else{
			$r="<ul>";
				
		}
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_list_item();	
			//$r.="</li>";	
		}
		$r.="</ul>";
		return $r;
	
	}
	function addToHtmlAsDromDownChildListItem(){

		/*
		$r=$this->get_html_as_li_open();
		$r.=$this->get_html_as_list_inner();
		//$r.=$this->get_html_children();
		$r.=$this->get_html_as_li_close();
		*/
	}
	function get_navlist_link_elem(){
		$c=new mwmod_mw_html_elem("a");
		if($str=$this->get_url()){
			$c->set_att("href",$str);
		}else{
			$c->set_att("href","#");
		}
		$c->addClass("nav-link");
		if($str=$this->get_param("aid")){
			$c->set_att("id",$str);
		}elseif($this->is_dropdown()){
			$c->set_att("id",$this->getElemID("ddctr"));
		}
		if($this->is_dropdown()){
			$c->addClass("dropdown-toggle");
			$c->set_att("data-bs-toggle","dropdown");
			$c->set_att("aria-expanded","false");
			$c->set_att("role","button");
		}
		
		if($str=$this->get_target()){
			$c->set_att("target",$str);
		}
		$e=$c->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());//revisar iconos
		return $c;

	}
	
	
	

	function get_li_class(){
		if(!$list=$this->get_li_class_list()){
			return false;	
		}
		return implode(" ",$list);
	}
	function get_html_as_li_open(){
		if($c=$this->get_li_class()){
			return "<li class='$c'>";	
		}
		return "<li>";	
	}
	function get_html_as_li_close(){
		return "</li>";	
	}
	
	function get_html_as_list_item(){
		$r=$this->get_html_as_li_open();
		$r.=$this->getHTMLElemAsDropdownChild()."";
		
		$r.=$this->get_html_as_li_close();
		return $r;
			
	}
	
	
	function get_html(){
		$r=$this->get_html_open();
		$r.=$this->get_alink();
		$r.=$this->get_html_children();
		$r.=$this->get_html_close();
		return $r;
			
	}
	function has_sub_items(){
		if($items=$this->get_items_allowed()){
			return true;	
		}
			
	}
	
	function get_html_children(){
		//modificar como top
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		if($this->is_dropdown()){
			
			$r="<ul class='dropdown-menu' role='menu'>";
				
		}else{
			$r="<ul>";
				
		}
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_list_item();	
			//$r.="</li>";	
		}
		$r.="</ul>";
		return $r;
	}
	final function init($cod,$etq,$parent,$url=false){
		if(is_a($parent,"mwmod_mw_mnu_mnuitem")){
			$mnu=$parent->mnu;
			$this->set_parent($parent);
		}else{
			$mnu=$parent;	
		}


		$this->cod=$cod;
		$this->mnu=$mnu;
		if($url){
			$this->url=$url;	
		}
		$this->etq=$etq;
		
		$ap=$mnu->mainap;
		$this->set_mainap($ap);	
	
	}
	
	function __toString(){
		return 	$this->get_alink();
	}
	function get_etq(){
		if($this->etq){
			return $this->etq;	
		}else{
			return $this->cod;		
		}
	}
	function get_url(){
		if($this->url){
			return $this->url;	
		}
	}
	function on_add($mainmnu){
		if($t=$mainmnu->auto_target){
			if(!isset($this->target)){
				$this->set_target($t);	
			}
		}
	}
	
	function set_target($val){
		if(!$val){
			unset($this->target);	
		}else{
			$this->target=$val;	
		}
	}
	function get_target(){
		return $this->target;	
	}
	function get_onclick(){
		return $this->onclick;	
	}
	function get_a_inner_html(){
		$r=$this->innerHTMLbefore;
		$r.=$this->get_etq();
		$r.=$this->innerHTMLafter;
		return $r;	
	}
	function get_html_for_tbl(){
		return $this->get_html();	
	}
	function get_alink(){
		
		$r="<a href='".$this->get_url()."'  ";
		if($str=$this->get_param("aid")){
			$r.="id='$str' ";	
		}
		if($str=$this->get_target()){
			$r.="target='$str' ";	
		}
		if($str=$this->get_onclick()){
			$q="'";
			if(strpos($str,"'")!==false){
				$q="\"";	
			}
			
			
			$r.="onclick=$q$str$q ";	
		}
		if($this->is_dropdown()){
			$r.=" class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' ";
			
		}else{
			if($this->tooltip){
				$r.="data-toggle='tooltip'  data-placement='top'  title='".mw_text_nl_js($this->tooltip)."' ";	
			}
	
		}
		
		
		$r.=">";
		$r.=$this->get_a_inner_html();
		if($this->is_dropdown()){
			$r.=" <span class='caret'></span>";
		}
		
		$r.="</a>";
				

		return 	$r;
	}
	//nuevas, mejoradas
	function get_html_as_topbarlist_item(){
		if($e=$this->get_htmlElem_as_topbarlist_item()){
			return $e->get_as_html();
		}
	}
	function get_htmlElem_as_topbarlist_item(){
		$c=new mwmod_mw_html_elem("li");
		$c->addClass("nav-item");
		if($this->is_dropdown()){
			$c->addClass("dropdown");
		}
		if($this->get_param("no-arrow")){
			$c->addClass("no-arrow");
		}
		if($e=$this->get_navlist_link_elem()){
			$c->add_cont($e);	
		}
		if($e=$this->get_htmlElem_as_topbar_children()){
			$c->add_cont($e);	
		}
		
		return $c;
		
		
	}
	function getHTMLElemAsDropdownChild(){
		$c=new mwmod_mw_html_elem("a");
		if($str=$this->get_url()){
			$c->set_att("href",$str);
		}else{
			$c->set_att("href","#");
		}
		
		$c->addClass("dropdown-item");
		if($str=$this->get_target()){
			$c->set_att("target",$str);
		}
		$e=$c->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());//revisar iconos
		return $c;
		
	}
	function get_htmlElem_as_topbar_children(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$c=new mwmod_mw_html_elem();
		$c->addClass("dropdown-menu dropdown-list dropdown-menu-right shadow animated--grow-in");
		$c->set_att("aria-labelledby",$this->getElemID("ddctr"));
		foreach ($items as $item){
			//$r.="<li>";	
			$c->add_cont($item->getHTMLElemAsDropdownChild());	
			//$r.="</li>";	
		}
		
		return $c;
	}

}

?>