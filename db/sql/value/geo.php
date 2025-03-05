<?php

abstract class mwmod_mw_db_sql_value_geo extends mwmod_mw_db_sql_value{
	function setAsGeo(){
		$this->paramQueryAllowed=false;

	}
	
	function validateLatitude($lat) {
        $lat = is_numeric($lat) ? (float)$lat : null;
        return !is_null($lat) && $lat >= -90 && $lat <= 90;
    }
   	function validateLongitude($lon) {
        $lon = is_numeric($lon) ? (float)$lon : null;
        return !is_null($lon) && $lon >= -180 && $lon <= 180;
    }
   function getValidatedLatLon($lat, $lon) {
        $lat = is_numeric($lat) ? (float)$lat : null;
        $lon = is_numeric($lon) ? (float)$lon : null;

        if ($this->validateLatitude($lat) && $this->validateLongitude($lon)) {
            return ['lat' => $lat, 'lon' => $lon];
        }

        return ['lat' => null, 'lon' => null];
    }
	
}


?>