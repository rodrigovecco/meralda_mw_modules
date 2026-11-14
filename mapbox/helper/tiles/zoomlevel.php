<?php
class  mwmod_mw_mapbox_helper_tiles_zoomlevel extends mw_apsubbaseobj{
	public $tilesSet;
	public $zoom;
	public $cod;
	private $tiles;
	function __construct($zoom,$tilesSet){
		$this->cod="z".$zoom;
		$this->zoom=$zoom;
		$this->tilesSet=$tilesSet;
	}
	function getShortData(){
		$r=array();
		$r["z"]=$this->zoom;
		$r["xList"]=implode(",", $this->createXArray());
		$r["yList"]=implode(",", $this->createYArray());
		return $r;		
	}
	function createXArray(){
		$first=$this->calcXWest();
		$last=$this->calcXEast();
		$max=$this->calcXMax();
		if($first>$max){
			$first=$max;
		}
		if($first<0){
			$first=0;
		}
		if($last>$max){
			$last=$max;
		}
		if($last<0){
			$last=0;
		}
		$r=array();
		if($last<$first){
			for($n=$first;$n<=$max;$n++){
				$r[$n]=$n;
			}
			for($n=0;$n<=$last;$n++){
				$r[$n]=$n;
			}
			return $r;

		}
		for($n=$first;$n<=$last;$n++){
			$r[$n]=$n;
		}
		return $r;		

	}
	function createYArray(){
		$first=$this->calcYNorth();
		$last=$this->calcYSouth();
		$max=$this->calcYMax();
		if($first>$max){
			$first=$max;
		}
		if($first<0){
			$first=0;
		}
		if($last>$max){
			$last=$max;
		}
		if($last<0){
			$last=0;
		}
		$r=array();
		if($last<$first){
			for($n=$first;$n<=$max;$n++){
				$r[$n]=$n;
			}
			for($n=0;$n<=$last;$n++){
				$r[$n]=$n;
			}
			return $r;

		}
		for($n=$first;$n<=$last;$n++){
			$r[$n]=$n;
		}
		return $r;		

	}
	function calcXMax(){
		return $this->tilesSet->xTile(180,$this->zoom);
	}
	function calcYMax(){
		return $this->tilesSet->yTile(-85,$this->zoom);
	}	
	function calcXWest(){
		return $this->tilesSet->xTile($this->tilesSet->getWestLng(),$this->zoom);
	}
	function calcXEast(){
		return $this->tilesSet->xTile($this->tilesSet->getEastLng(),$this->zoom);
	}
	function calcYNorth(){
		return $this->tilesSet->yTile($this->tilesSet->getNorthLat(),$this->zoom);
	}
	function calcYSouth(){
		return $this->tilesSet->yTile($this->tilesSet->getSouthLat(),$this->zoom);
	}
	function getDebugData(){
		$r=array();
		$r["zoom"]=$this->zoom;
		$r["XWest"]=$this->calcXWest();
		$r["XEast"]=$this->calcXEast();
		$r["YNorth"]=$this->calcYNorth();
		$r["YSouth"]=$this->calcYSouth();
		$r["XMax"]=$this->calcXMax();
		//$r["XMin"]=$this->tilesSet->xTile(-180,$this->zoom);
		$r["YMax"]=$this->calcYMax();
		//$r["YMin"]=$this->tilesSet->yTile(85,$this->zoom);

		$r["xList"]=implode(",", $this->createXArray());
		$r["yList"]=implode(",", $this->createYArray());
		
		$this->__get_priv_tiles();
		if($items=$this->tiles->get_items()){
			$r["tiles"]=array();
			foreach($items as $c=>$item){
				$r["tiles"][$c]=$item->getDebugData();
			}
		}
		
		return $r;
	}
	function get_cod(){
		return $this->cod;
	}
	function createTiles($list){
		$yList=$this->createYArray();
		$xList=$this->createXArray();
		foreach($yList as $y){
			reset($xList);
			foreach($xList as $x){
				$obj=new mwmod_mw_mapbox_helper_tiles_tile($x,$y,$this);
				$list->add_item($obj);
			}
		}
	}
	function __get_priv_tiles(){
		if(!isset($this->tiles)){
			$this->tiles=new mwmod_mw_util_itemsbycod();
			$this->createTiles($this->tiles);
		}
		return $this->tiles;
	}

	
}
?>