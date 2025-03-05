<?php
class mwmod_mw_bootstrap_man extends mw_apsubbaseobj{
	private static $_icons;
	
	
	function createIcons(){
		$r=array();
		
		$this->createIconsFromList("glyphicon","glyphicon",$r);
		//$this->createIconsFromList("glyphicon","glyphicon_sb",$r);
		//$this->createIconsFromList("glyphicon","glyphicon_bt",$r);
		$this->createIconsFromList("fa","fa",$r);
		$this->createIconsFromList("fab","fa_brands",$r,"fa");
		
		return $r;
			
	}
	function createIconsFromList($listCod,$filename=false,&$result=array(),$classPref=false){
		if(!$result){
			$result=array();
		}
		if(!$pathman=$this->mainap->get_path_man("system")){
			return false;	
		}
		if(!$filename){
			$filename=$listCod;	
		}
		$file=$pathman->get_file_path_if_exists("{$filename}.txt","data/bootstrap/icons");
		if(!$file){
			return false;
		}
		if(!$handle = fopen($file, "r")){
			return false;	
		}
		$r=array();
		while (($line = fgets($handle)) !== false) {
			if($line=trim($line)){
				if(strpos($line,"/")!==0){
					$a=explode(" ",$line,2);
					if($cod=trim($a[0]??null)){
						$name=trim($a[1]??"");
						$icon= new mwmod_mw_bootstrap_icons_icon();
						if($classPref){
							$icon->classPref=$classPref;
						}
						$icon->setInfo($listCod,$cod,$name);
						$icon->filecod=$filename;
						$result[]=$icon;
						$r[]=$icon;
						
						
					}
				}
					
			}
		}

		fclose($handle);
		
		
		
		return $r;

		
		
	}
	function sortIcons($a,$b){
		$al = strtolower($a->get_name());
        $bl = strtolower($b->get_name());
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;

	}
	function loadIcons(){
		$r=array();
		if($items=$this->createIcons()){
			foreach($items as $item){
				$r[$item->cod]=$item;	
			}
		}
		uasort($r, array($this, 'sortIcons')); 
		return $r;
	}
	
	function getIcons(){
		return $this->_getIcons();	
	}
	final protected function _getIcons(){
		if(!self::$_icons){
			
			self::$_icons=$this->loadIcons();
		}
		return self::$_icons;
		
	}

}
?>