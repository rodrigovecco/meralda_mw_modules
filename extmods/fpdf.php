<?php
$subpathman=mw_get_main_ap()->get_sub_path_man("modulesext/fpdf","system");
if($fullpath=$subpathman->get_file_path_if_exists("fpdf.php")){
	require_once $fullpath;
}
class mwmod_mw_extmods_fpdf extends FPDF{
	function countCharsToFitWidth($text, $width) {
        
        $textWidth = $this->GetStringWidth($text);
        $charCount = strlen($text);
                        
        while ($textWidth > $width && $charCount > 0) {
            $text = substr($text, 0, $charCount - 1);
            $charCount--;
            $textWidth = $this->GetStringWidth($text);
        }
                        
        return $charCount;
    }
    function cutTextToFitWidth($text, $width,$append="...") {
    	
    	$text=$text."";
    	$len=strlen($text);
    	$maxLen=$this->countCharsToFitWidth($text, $width);
    	if($len>$maxLen){
    		return substr($text, 0, $maxLen).$append;
    	}
    	return $text;
    }
	function decodeText($txt){
        $txt=$txt??''; 
        $txt=$txt."";
		return mb_convert_encoding($txt, 'ISO-8859-1','UTF-8' );
		//return utf8_decode($txt);//utf8_decode?
	}
}

?>