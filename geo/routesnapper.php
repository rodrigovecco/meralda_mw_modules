<?php
class mwmod_mw_geo_routesnapper extends mw_apsubbaseobj{
    const R = 6371000.0; // metros
    const DEG2RAD = M_PI / 180.0;
    const RAD2DEG = 180.0 / M_PI;

    private $route = []; // [['lat'=>..,'lng'=>..], ...]

    public function __construct($route){
		if($route){
			$this->setRoute($route);
		
		}
       
    }
    final function __get_priv_route(){ return $this->route; }

    /** Acepta:
     *  - [[lng, lat], [lng, lat], ...]  ← tu caso
     *  - [['lat'=>..,'lng'=>..], ...]
     *  - mezcla de ambos (normaliza todo)
     */
    public function setRoute(array $route){
        if(!is_array($route)){ return false; }
        $norm = [];
        foreach($route as $p){
            // formato numerico [lng, lat]
            if(is_array($p) && isset($p[0], $p[1])){
                $lng = (float)$p[0];
                $lat = (float)$p[1];
            }elseif(is_array($p) && isset($p['lat'], $p['lng'])){ // formato asociativo
                $lat = (float)$p['lat'];
                $lng = (float)$p['lng'];
            }else{
                continue; // punto inválido
            }
            if(!$this->isValidCoord($lat, $lng)){ continue; }

            // evita duplicados consecutivos exactos
            $last = end($norm);
            if($last && abs($last['lat'] - $lat) < 1e-12 && abs($last['lng'] - $lng) < 1e-12){
                continue;
            }
            $norm[] = ['lat'=>$lat, 'lng'=>$lng];
        }
        if(count($norm) < 2){ return false; }
        $this->route = $norm;
        return true;
    }

    private function isValidCoord($lat, $lng){
        return is_finite($lat) && is_finite($lng) &&
               ($lat >= -90 && $lat <= 90) &&
               ($lng >= -180 && $lng <= 180);
    }

    public function checkRoute(){
        return is_array($this->route) && count($this->route) >= 2 &&
               isset($this->route[0]['lat'], $this->route[0]['lng']);
    }

    public function snapPoint(float $alat, float $alon){
        if(!$this->checkRoute()){ return false; }

        $lat0 = $alat * self::DEG2RAD;
        $lon0 = $alon * self::DEG2RAD;
        $cosLat0 = cos($lat0);

        $toXY = function($lat, $lng) use ($lat0, $lon0, $cosLat0) {
            $x = (($lng * self::DEG2RAD) - $lon0) * $cosLat0 * self::R;
            $y = (($lat * self::DEG2RAD) - $lat0) * self::R;
            return [$x, $y];
        };
        $toLatLon = function($x, $y) use ($lat0, $lon0, $cosLat0) {
            $lat = ($y / self::R + $lat0) * self::RAD2DEG;
            $lng = ($x / (self::R * $cosLat0) + $lon0) * self::RAD2DEG;
            return [$lat, $lng];
        };
        $haversine = function($lat1, $lng1, $lat2, $lng2) {
            $φ1 = $lat1 * self::DEG2RAD; $λ1 = $lng1 * self::DEG2RAD;
            $φ2 = $lat2 * self::DEG2RAD; $λ2 = $lng2 * self::DEG2RAD;
            $dφ = $φ2 - $φ1; $dλ = $λ2 - $λ1;
            $s = sin($dφ/2)**2 + cos($φ1)*cos($φ2)*sin($dλ/2)**2;
            return 2 * self::R * asin(min(1.0, sqrt($s)));
        };

        $bestDist = INF;
        $bestLat = $this->route[0]['lat'];
        $bestLng = $this->route[0]['lng'];
        $bestSeg = 0;
        $bestProgress = 0.0;

        [$Ax, $Ay] = $toXY($alat, $alon);
        $accum = 0.0;

        for ($i = 0; $i < count($this->route) - 1; $i++) {
            $P = $this->route[$i];
            $Q = $this->route[$i+1];

            [$Px, $Py] = $toXY($P['lat'], $P['lng']);
            [$Qx, $Qy] = $toXY($Q['lat'], $Q['lng']);

            $ABx = $Qx - $Px; $ABy = $Qy - $Py;
            $APx = $Ax - $Px; $APy = $Ay - $Py;

            $ab2 = $ABx*$ABx + $ABy*$ABy;
            if ($ab2 == 0.0) { continue; }

            $t = ($APx*$ABx + $APy*$ABy) / $ab2;
            if ($t < 0.0) $t = 0.0;
            if ($t > 1.0) $t = 1.0;

            $Sx = $Px + $t*$ABx;
            $Sy = $Py + $t*$ABy;
            [$Blat, $Blng] = $toLatLon($Sx, $Sy);

            $distToSeg = $haversine($alat, $alon, $Blat, $Blng);
            if ($distToSeg < $bestDist) {
                $bestDist = $distToSeg;
                $bestLat = $Blat;
                $bestLng = $Blng;
                $bestSeg = $i;
                $bestProgress = $accum + $haversine($P['lat'], $P['lng'], $Blat, $Blng);
            }

            $accum += $haversine($P['lat'], $P['lng'], $Q['lat'], $Q['lng']);
        }

        return [
            'snapped_lat'      => $bestLat,
            'snapped_lon'      => $bestLng,  // ojo: tu DB usa snapped_lng, aquí devuelvo snapped_lon como antes
            'segment_index'    => $bestSeg,
            'route_deviation_m'=> $bestDist,
            'route_progress_m' => $bestProgress
        ];
    }
}


?>