<?php
class  mwmod_mw_mapbox_helper_tiles_tile extends mw_apsubbaseobj{
	public $tilesSet;
	public $zoomLevel;
	public $cod;
	public $x;
	public $y;
	public $z;
	function __construct($x,$y,$zoomLevel){
		$this->cod="z".$zoom."_x".$x."_y".$y;
		$this->zoomLevel=$zoomLevel;
		$this->tilesSet=$this->zoomLevel->tilesSet;
		$this->x=$x;
		$this->y=$y;
		$this->z=$zoomLevel->zoom;
	}

	
	function getDebugData(){
		$r=array();
		$r["x"]=$this->x;
		$r["y"]=$this->y;
		$r["z"]=$this->z;
		if($url=$this->tilesSet->getTileURL($this)){
			$r["url"]=$url;
		}

		return $r;
	}
	function get_cod(){
		return $this->cod;
	}

	
}
?>