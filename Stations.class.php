<?php
/**
 * This is a class which will return all available Haltes from De Lijn
 * 
 * @package packages/Haltes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */

include_once('tools.php');
 
class DeLijnStations extends AReader{

    public static function getParameters(){
		return array("municipal" => "The region to request the De Lijn stops from");
    }

    public static function getRequiredParameters(){
		return array("municipal");
    }

    public function setParameter($key,$val){
        if($key == "municipal"){
            $this->municipal = strtoupper($val);
        }
    }

    public function read(){
		date_default_timezone_set("Europe/Brussels");
        $arguments = array(":municipal" => urldecode($this->municipal));
        $result = R::getAll("select * from DL_Stops where STOPPARENTMUNICIPAL like :municipal or STOPMUNICIPAL like :municipal and STOPISPUBLIC = 'true'",$arguments);
		
		$results = array();
        foreach($result as &$row){
            $station = array();
            $station["id"] = $row["STOPIDENTIFIER"];
            $station["name"] = $row["STOPDESCRPTION"];
            $station["longitude"] = $row["STOPLONGITUDE"];
            $station["latitude"] = $row["STOPLATITUDE"];
            
            $results[] = $station;
        }
        date_default_timezone_set("UTC");
        return $results;
    }

    public static function getDoc(){
		return "This resource contains haltes from De Lijn.";
    }
}

?>