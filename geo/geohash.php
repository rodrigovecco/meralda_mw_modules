<?php

/**
 * @author  Saikiran Ch <saikiranchavan@gmail.com>
 * @class   Geohash
 * @description  Algorithm to encode geographic coordinates to a string of letters and digits
 * Modified bt RVH
 */
class mwmod_mw_geo_geohash{
    const NORTH = 0;
    const EAST = 1;
    const SOUTH = 2;
    const WEST = 3;

    const EVEN = 0;
    const ODD = 1;

    public $defaultGeohashLength=5;

    /**
     * Used for decoding the hash from base32
     */
    protected $base32Mapping = "0123456789bcdefghjkmnpqrstuvwxyz";

    private $borderChars = [
        self::EVEN => [
            self::NORTH => 'bcfguvyz',
            self::EAST => 'prxz',
            self::SOUTH => '0145hjnp',
            self::WEST => '028b',
        ]
    ];

    private $neighborChars = [
        self::EVEN => [
            self::NORTH => '238967debc01fg45kmstqrwxuvhjyznp',
            self::EAST => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
            self::SOUTH => 'bc01fg45238967deuvhjyznpkmstqrwx',
            self::WEST => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
        ],
    ];

    public function __construct() {
        $this->neighborChars[self::ODD] = array(
            self::NORTH => $this->neighborChars[self::EVEN][self::EAST],
            self::EAST => $this->neighborChars[self::EVEN][self::NORTH],
            self::SOUTH => $this->neighborChars[self::EVEN][self::WEST],
            self::WEST => $this->neighborChars[self::EVEN][self::SOUTH],
        );

        $this->borderChars[self::ODD] = array(
            self::NORTH => $this->borderChars[self::EVEN][self::EAST],
            self::EAST => $this->borderChars[self::EVEN][self::NORTH],
            self::SOUTH => $this->borderChars[self::EVEN][self::WEST],
            self::WEST => $this->borderChars[self::EVEN][self::SOUTH],
        );
    }

    /**
     * Encode the latitude and longitude into a hashed string
     * @param  float   Latitude
     * @param  float   longitude
     * @param  int     GeohashLength
     * @return string  Hashed string obtained from the coordinates
     */
    public function encode($latitude, $Longitude, $geohashLength = null) {
        if(!$geohashLength){
            $geohashLength=$this->defaultGeohashLength;
        }
        // Get latitude and longitude bits length from given geohash Length
        if ($geohashLength % 2 == 0) {
            $latBitsLength = $lonBitsLength = ($geohashLength/2) * 5;
        } else {
            $latBitsLength = (ceil($geohashLength / 2) * 5) - 3;
            $lonBitsLength = $latBitsLength + 1;
        }

        // Convert the coordinates into binary format
        $binaryString = "";
        $latbits = $this->getBits($latitude, -90, 90, $latBitsLength);
        $lonbits = $this->getBits($Longitude, -180, 180, $lonBitsLength);
        $binaryLength = strlen($latbits) + strlen($lonbits);

        // Combine the lat and lon bits and get the binaryString
        for ($i=1 ; $i < $binaryLength + 1; $i++) {
            if ($i%2 == 0) {
                $pos = (int)($i-2)/2;
                $binaryString .= $latbits[$pos];
            } else {
                $pos = (int)floor($i/2);
                $binaryString .= $lonbits[$pos];
            }
        }

        // Convert the binary to hash
        $hash = "";
        for ($i=0; $i< strlen($binaryString); $i+=5) {
            $n = bindec(substr($binaryString,$i,5));
            $hash = $hash . $this->base32Mapping[$n];
        }
        return $hash;
    }

    /**
     * Decode the Geohash into geographic coordinates
     * @param   string  $hash
     * @param   double  Percentage error
     * @return  mixed   Array of Latitude and Longitude
     */
    public function decode($hash, $error = false)    {
        $hash = strtolower(trim($hash));
        if(!$this->isValidGeohash($hash)){
            return array(0,0);
        }
        $hashLength = strlen($hash);
        $latlonbits = "";
    
        // Convert geohash to binary representation
        foreach (str_split($hash) as $g) {
            $position = strpos($this->base32Mapping, $g);
            $latlonbits .= str_pad(decbin($position), 5, "0", STR_PAD_LEFT);
        }

        $latbits = $lonbits = "";

        // Even bits go to longitude, odd bits to latitude
        $binaryLength = strlen($latlonbits);
        for ($i = 0; $i < $binaryLength; $i++) {
            if ($i % 2 == 0) {
                $lonbits .= $latlonbits[$i];
            } else {
                $latbits .= $latlonbits[$i];
            }
        }

        // Get latitude and longitude
        $latitude = $this->getCoordinate(-90, 90, $latbits);
        $longitude = $this->getCoordinate(-180, 180, $lonbits);

        // Round coordinates based on hash precision
        $latitude = round($latitude, $hashLength - 2);
        $longitude = round($longitude, $hashLength - 2);

        return [$latitude, $longitude];
























        ////////this was corrected by rvh
        /*
        echo "*{$hash}*".strtoupper($hash);
        $hash=$hash."";
        $hashLength = strlen($hash);
        
        $latlonbits = base_convert($hash, 32, 2);
        $binaryLength = strlen($latlonbits);
        $latbits = "";
        $lonbits = "";

        $geohashArray = str_split($hash, 1);
        $latlonbits = "";
        foreach($geohashArray as $g) {
            if (($position = stripos($this->base32Mapping, $g)) !== FALSE) {
                $latlonbits .= str_pad(decbin($position), 5, "0", STR_PAD_LEFT);
            } else {
                $latlonbits .= "00000";
            }
        }

        // Even bits take latitude Code
        // Odd bits take longitude Code
        for ($i = 0; $i < $binaryLength; $i++) {
            ($i % 2 == 0) ? ($lonbits .= $latlonbits[$i]) : ($latbits .= $latlonbits[$i]);
        }

        // Get the Coordinates
        $latitude = $this->getCoordinate(-90, 90, $latbits);
        $longitude = $this->getCoordinate(-180, 180, $lonbits);

        // Rounding the latitude and longitude values
        $latitude = round($latitude, $hashLength - 2);
        $longitude = round($longitude, $hashLength - 2);
        return array($latitude, $longitude);
        */
    }

    /**
     * Get the Geographic Coordinate from the binaryString
     * @param  float   $min
     * @param  float   $max
     * @param  string  $binaryString
     * @return float   $coordinate
     */
    public function getCoordinate($min, $max, $binaryString)    {
        $error = 0;
        for ($i = 0; $i < strlen($binaryString); $i++) {
            $mid = ($min + $max)/2 ;
            if ($binaryString[$i] == 1){
                $min = $mid ;
            } elseif ($binaryString[$i] == 0) {
                $max = $mid;
            }
            $value = ($min + $max)/2;
            $error = $value - $error;
        }
        return $value;
    }

    /**
     * Convert coordinate into binary string according to required bits length
     * @param  float   Coordinate
     * @param  int     minimum value
     * @param  int     maximum value
     * @param  int     bitslength
     * @return string  binary string for the given coordinate
     */
    public function getBits($coordinate, $min, $max, $bitsLength)    {
        $binaryString = "";
        $i = 0;
        while ($bitsLength > $i) {
            $mid = ($min+$max)/2;
            if ($coordinate > $mid) {
                $binaryString .= "1";
                $min = $mid;
            } else {
                $binaryString .= "0";
                $max = $mid;
            }
            $i++;
        }
        return $binaryString;
    }

    /**
     * Computes neighboring geohash values for given geohash.
     *
     * @param string $hash
     * @return array
     */
    public function getNeighbors($hash) {
        $hashNorth = $this->calculateNeighbor($hash, self::NORTH);
        $hashEast = $this->calculateNeighbor($hash, self::EAST);
        $hashSouth = $this->calculateNeighbor($hash, self::SOUTH);
        $hashWest = $this->calculateNeighbor($hash, self::WEST);

        $hashNorthEast = $this->calculateNeighbor($hashNorth, self::EAST);
        $hashSouthEast = $this->calculateNeighbor($hashSouth, self::EAST);
        $hashSouthWest = $this->calculateNeighbor($hashSouth, self::WEST);
        $hashNorthWest = $this->calculateNeighbor($hashNorth, self::WEST);
        return [
            'North'     => $hashNorth,
            'East'      => $hashEast,
            'South'     => $hashSouth,
            'West'      => $hashWest,
            'NorthEast' => $hashNorthEast,
            'SouthEast' => $hashSouthEast,
            'SouthWest' => $hashSouthWest,
            'NorthWest' => $hashNorthWest,
        ];
    }

    /**
     * Calculates neighbor geohash for given geohash and direction
     *
     * @param string $hash
     * @param string $direction
     * @return string $neighborHash
     */
    private function calculateNeighbor($hash, $direction) {
        $length = strlen($hash);
        if ($length == 0) {
            return '';
        }
        $lastChar = $hash[$length - 1];
        $evenOrOdd = ($length - 1) % 2;
        $baseHash = substr($hash, 0, -1);
        if (strpos($this->borderChars[$evenOrOdd][$direction], $lastChar) !== false) {
            $baseHash = $this->calculateNeighbor($baseHash, $direction);
        }
        return $baseHash . $this->neighborChars[$evenOrOdd][$direction][strpos($this->base32Mapping, $lastChar)];
    }



    //added by RVH
    /**
     * Decode the Geohash into geographic coordinates
     * @param   string  $hash
     * @param   double  Percentage error
     * @return  mixed   Indexed Array of Latitude and Longitude
     */
    public function decodeIndexed($hash, $error = false)    {
        if($r=$this->decode($hash,$error)){
            return $this->getIndexedPoint($r);
        }
    }
    public function getIndexedPoint($latLng){
        if(is_array($latLng)){
           return array("lat"=>$latLng[0],"lng"=>$latLng[1]); 
        }
    }

    /**
     * Get the 4 corner points (NW, NE, SW, SE) of a geohash
     * @param string $hash The geohash string
     * @return array An associative array with keys 'NW', 'NE', 'SW', 'SE'
     */
    public function getCorners($hash,$indexed=false) {
        $decoded = $this->decode($hash);
        $lat = $decoded[0];
        $lon = $decoded[1];

        // Calculate bounds for the geohash
        $hashLength = strlen($hash);
        $latError = 90 / pow(2, ceil($hashLength / 2) * 5);
        $lonError = 180 / pow(2, floor($hashLength / 2) * 5);

        // Calculate corner points
        $corners = [
            'NW' => [$lat + $latError, $lon - $lonError],
            'NE' => [$lat + $latError, $lon + $lonError],
            'SW' => [$lat - $latError, $lon - $lonError],
            'SE' => [$lat - $latError, $lon + $lonError],
        ];
        if($indexed){
            $r=array();
            foreach($corners as $c=>$ll){
                $r[$c]=$this->getIndexedPoint($ll);
            }
            return $r;
        }

        return $corners;
    }
    /**
     * Verify if a given geohash is valid
     * @param string $hash The geohash string
     * @param int $minLength Minimum allowed length
     * @param int $maxLength Maximum allowed length
     * @return bool True if valid, false otherwise
     */
    public function isValidGeohash($hash,  $maxLength = 12,$minLength = 1) {
        // Check if input is a string
        if (!is_string($hash)) {
            return false;
        }

        $length = strlen($hash);

        // Check length constraints
        if ($length < $minLength || $length > $maxLength) {
            return false;
        }

        // Check if all characters are valid base32 characters
        for ($i = 0; $i < $length; $i++) {
            if (strpos($this->base32Mapping, $hash[$i]) === false) {
                return false;
            }
        }

        return true;
    }


}
