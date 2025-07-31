<?php
/**
 * @property-read mwmod_mw_geo_geohash $geoHash
 */
class mwmod_mw_geo_helper extends mw_apsubbaseobj{
    private $geoHash;
    public $defaultGeohashLength=5;

    function __construct(){
    
    }
   function normalizeWKTToLineArray($wkt) {
        // Try to parse as POLYGON
        if ($points = $this->parseWKTPolygon($wkt)) {
            // If last point == first, remove it (polygons are closed)
            if (count($points) > 1) {
                $first = $points[0];
                $last = end($points);
                if ($first[0] === $last[0] && $first[1] === $last[1]) {
                    array_pop($points);
                }
            }
        } elseif ($points = $this->parseWKTLineStringToPolygon($wkt)) {
            // Already a linestring
        } else {
            return false;
        }

        // Validate at least 2 points
        if (count($points) < 2) {
            return false;
        }

        return $points;
    }
    function lineArrayToWKT(array $points) {
        if (count($points) < 2) {
            return false;
        }

        $strPoints = [];
        foreach ($points as $p) {
            $strPoints[] = "{$p[1]} {$p[0]}"; // lng lat
        }

        return "LINESTRING (" . implode(', ', $strPoints) . ")";
    }

    /*Poligons*/
    function polygonToJSArray(array $points) {
        $jsPoints = [];
        foreach ($points as $p) {
            $jsPoints[] = ['lat' => $p[1], 'lng' => $p[0]];
        }
        return $jsPoints;
    }
    function normalizeWKTToClosedPolygon($wkt){
        if(!$points=$this->parseWKTPolygon($wkt)){
            $points=$this->parseWKTLineStringToPolygon($wkt);
        }
        if(!$points){
            return false;
        }
        if(!$points=$this->validatePolygonCoords($points)){
            return false;
        }
        return $this->closePolygon($points);
        
       


    }
    function parseWKTPolygon($wkt) {
        if(!is_string($wkt)){
            return false;
        }
        if (!preg_match('/^POLYGON\s*\(\((.+)\)\)$/i', trim($wkt), $matches)) {
            return false;
        }
    
        $coordsStr = $matches[1];
        $points = [];
        foreach (explode(',', $coordsStr) as $pair) {
            $parts = preg_split('/\s+/', trim($pair));
            if (count($parts) != 2) {
                continue;
            }
            $lat = floatval($parts[1]);
            $lng = floatval($parts[0]);
            $points[] = [$lat, $lng];
        }
    
        return $points;
    }
    function parseWKTLineStringToPolygon($wkt) {
        if(!is_string($wkt)){
            return false;
        }
        if (!preg_match('/^LINESTRING\s*\((.+)\)$/i', trim($wkt), $matches)) {
            return false;
        }
    
        $coordsStr = $matches[1];
        $points = [];
        foreach (explode(',', $coordsStr) as $pair) {
            $parts = preg_split('/\s+/', trim($pair));
            if (count($parts) != 2) continue;
            $lat = floatval($parts[1]);
            $lng = floatval($parts[0]);
            $points[] = [$lat, $lng];
        }
        return $points;
        // Validar y cerrar
       
    }
    function validatePolygonCoords(array $points) {
        $valid = [];
        foreach ($points as $p) {
            if (!is_array($p) || count($p) !== 2) continue;
            list($lat, $lng) = $p;
            if ($this->validateCoordinates($lat, $lng)) {
                $valid[] = [$lat, $lng];
            }
        }
        return count($valid) >= 3 ? $valid : false;
    }
    function closePolygon(array $points) {
        if (count($points) < 3) return false;
        $first = $points[0];
        $last = end($points);
        if ($first[0] != $last[0] || $first[1] != $last[1]) {
            $points[] = $first;
        }
        return $points;
    }
    function polygonToWKT(array $points) {
        $strPoints = [];
        foreach ($points as $p) {
            $strPoints[] = "{$p[1]} {$p[0]}"; // lng lat
        }
        return "POLYGON ((" . implode(', ', $strPoints) . "))";
    }
















    /*Coordinates*/
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