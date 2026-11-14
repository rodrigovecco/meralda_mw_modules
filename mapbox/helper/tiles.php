<?php
//https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames
//todo: falta ver negativos en caso de que bounds pasen los puntos 0!!
class  mwmod_mw_mapbox_helper_tiles extends mw_apsubbaseobj{
	public $minZoomLevel=0;
	public $maxZoomLevel=19;
	public $bound1lat;//N
	public $bound1lng;//E
	public $bound2lat;//S
	public $bound2lng;//W
	private $zoomLevels;
	public $rasterLayerID;
	public $accessToken;

	function __construct(){
		
	}
	function getTileURLFromZXY($z,$x,$y){
		//extender
		if(!$this->rasterLayerID){
			return false;
		}
		$r="https://api.mapbox.com/v4/".$this->rasterLayerID."/".$z."/".$x."/".$y.".png";
		if($this->accessToken){
			$r.="?access_token=".$this->accessToken;
		}
		return $r;


	}
	function getTileURL($tile){
		return $this->getTileURLFromZXY($tile->z,$tile->x,$tile->y);
	}
	function getShortData(){
		$r=array();
		$this->__get_priv_zoomLevels();
		if($items=$this->zoomLevels->get_items()){
			$r["zoomLevels"]=array();
			foreach($items as $c=>$item){
				$r["zoomLevels"][]=$item->getShortData();
			}
		}
		return $r;		
	}
	function setZoomLevels($minZoomLevel,$maxZoomLevel){
		$this->minZoomLevel=abs(round($minZoomLevel+0));
		if($this->minZoomLevel>19){
			$this->minZoomLevel=19;
		}
		$this->maxZoomLevel=abs(round($maxZoomLevel+0));
		if($this->maxZoomLevel>19){
			$this->maxZoomLevel=19;
		}
		if($this->maxZoomLevel<$this->minZoomLevel){
			$this->maxZoomLevel=$this->minZoomLevel;
		}		

	}
	function getNorthLat(){
		return $this->bound1lat;
	}
	function getEastLng(){
		return $this->bound1lng;
	}
	function getSouthLat(){
		return $this->bound2lat;
	}
	function getWestLng(){
		return $this->bound2lng;
	}
	/*
	function normalizeLat($lat){
		$max=85;
		$f=1;
		$coor=$lat;
		if($coor<0){
			$f=-1;
		}


	}
	*/

	function setBounds($bound1lat,$bound1lng,$bound2lat,$bound2lng){




		$this->bound1lat=$bound1lat;
		$this->bound1lng=$bound1lng;
		$this->bound2lat=$bound2lat;
		$this->bound2lng=$bound2lng;


	}
	function xTile($lon,$zoom){ 
		$xtile = floor((($lon + 180) / 360) * pow(2, $zoom));
		return $xtile;
	}
	function yTile($lat,$zoom){ 
		$ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
		return $ytile;
	}
	/*
	$n = pow(2, $zoom);
	$lon_deg = $xtile / $n * 360.0 - 180.0;
	$lat_deg = rad2deg(atan(sinh(pi() * (1 - 2 * $ytile / $n))));
	*/
	function getDebugData(){
		$r=array();
		$this->__get_priv_zoomLevels();
		if($items=$this->zoomLevels->get_items()){
			$r["zoomLevels"]=array();
			foreach($items as $c=>$item){
				$r["zoomLevels"][$c]=$item->getDebugData();
			}
		}
		return $r;
	}
	function createZoomLevels($list){
		for($i=$this->minZoomLevel;$i<=$this->maxZoomLevel;$i++){
			$obj=new mwmod_mw_mapbox_helper_tiles_zoomlevel($i,$this);
			$list->add_item($obj);
		}

	}
	function __get_priv_zoomLevels(){
		if(!isset($this->zoomLevels)){
			$this->zoomLevels=new mwmod_mw_util_itemsbycod();
			$this->createZoomLevels($this->zoomLevels);
		}
		return $this->zoomLevels;
	}
}
?>