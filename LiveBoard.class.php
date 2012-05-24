<?php
/**
 * This is a class which will return the information with the latest departures from a certain station
 * 
 * @package packages/LiveBoard
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */

include_once('LiveBoardDao.php');
 
class DeLijnLiveBoard extends AReader{

	public function __construct($package, $resource, $RESTparameters) {
		parent::__construct($package, $resource, $RESTparameters);
		
		// Initialize possible params
		$this->stationid = null;
		$this->offset = null;
		$this->rowcount = null;
	}

    public static function getParameters(){
		return array("offset" => "Offset"
						,"rowcount" => "Rowcount");
    }

    public static function getRequiredParameters(){
		return array("Station ID" => "stationid");
    }

    public function setParameter($key,$val){
        if ($key == "stationid"){
			$this->stationid = $val;
		} else if ($key == "offset"){
			$this->offset = $val;
		} else if ($key == "rowcount"){
			$this->rowcount = $val;
		}
    }

    public function read(){
		$liveBoardDao = new LiveBoardDao();
	
		return $liveBoardDao->getLiveBoard($this->stationid, $this->offset, $this->rowcount);
    }

    public static function getDoc(){
		return "This resource contains a LiveBoard for a certain Station from De Lijn.";
    }
}

?>