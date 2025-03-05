<?php
class mwmod_mw_geo_helper extends mw_apsubbaseobj{
    private $geoHash;
    public $defaultGeohashLength=5;

    function __construct(){
    
    }
    function validateCoordinates($latitude, $longitude) {
        return $this->validateLatitude($latitude) && $this->validateLongitude($longitude);
    }
    function validateLatitude($latitude) {
        return is_numeric($latitude) && $latitude >= -90 && $latitude <= 90;
    }
    function validateLongitude($longitude) {
        return is_numeric($longitude) && $longitude >= -180 && $longitude <= 180;
    }
    final function __get_priv_geoHash(){
        if(!isset($this->geoHash)){
            $this->geoHash=new mwmod_mw_geo_geohash();
            $this->geoHash->defaultGeohashLength=$this->defaultGeohashLength;
        }
        return $this->geoHash;
    }
    function geodesicDistance($lat1, $lon1, $lat2, $lon2) {
        if(!$this->validateCoordinates($lat1, $lon1) || !$this->validateCoordinates($lat2, $lon2)){
            return false;
        }
        return $this->_geodesicDistance($lat1, $lon1, $lat2, $lon2);

    
    }
    private function _geodesicDistance($lat1, $lon1, $lat2, $lon2) {
        // Earth's radius in meters
        $earthRadius = 6371000;
        
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        // Compute differences
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;
        
        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Distance in meters
        return $earthRadius * $c;
    }
}
?>