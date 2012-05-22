<?php
/**
 * This is a class which will return all available Haltes from De Lijn
 * 
 * @package packages/Haltes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */

include_once('stationDao.php');
 
class DeLijnStations extends AReader{

	public function __construct($package, $resource, $RESTparameters) {
		parent::__construct($package, $resource, $RESTparameters);
		
		// Initialize possible params
		$this->longitude = null;
		$this->latitude = null;
		$this->offset = null;
		$this->rowcount = null;
	}

    public static function getParameters(){
		return array("longitude" => "Longitude"
						,"latitude" => "Latitude"
						,"offset" => "Offeset"
						,"rowcount" => "Rowcount");
    }

    public static function getRequiredParameters(){
		return array();
    }

    public function setParameter($key,$val){
        if ($key == "longitude"){
			$this->longitude = $val;
		} else if ($key == "latitude"){
			$this->latitude = $val;
		} else if ($key == "offset"){
			$this->offset = $val;
		} else if ($key == "rowcount"){
			$this->rowcount = $val;
		}
    }

    public function read(){
		$stationDao = new StationDao();
		
		if($this->longitude != null && $this->latitude != null) {
			return $stationDao->getClosestStations($this->longitude, $this->latitude);
		}
	
		return $stationDao->getAllStations($this->offset, $this->rowcount);
    }

    public static function getDoc(){
		return "This resource contains haltes from De Lijn.";
    }
}

?>