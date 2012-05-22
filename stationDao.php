<?php

include_once('tools.php');

/**
 * This is a class which will return all available Haltes from De Lijn
 * 
 * @package packages/Haltes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */
class StationDao {
	/**
	  * Query to get all stations ordered alphabetically
	  * @param string latitude
	  * @param string longitude
	  */
	private $GET_ALL_STATIONS_QUERY = "SELECT stop_id, stop_name, stop_lat, stop_lon 
								FROM dlgtfs_stops
								ORDER BY stop_name ASC";

	/**
	  * Query to get all closest station to a given point (lat/long) ordered by distance
	  * @param string latitude
	  * @param string longitude
	  */
	private $GET_CLOSEST_STATIONS_QUERY = "SELECT stop_id, stop_name, stop_lat, stop_lon, 
									( 6371 * acos( cos( radians(:latitude) ) 
												   * cos( radians( stop_lat ) ) 
												   * cos( radians( :longitude ) 
													   - radians(stop_lon) ) 
												   + sin( radians(:latitude) ) 
												   * sin( radians( stop_lat ) ) 
												 )
								   ) AS distance 
								FROM dlgtfs_stops 
								HAVING distance < 250000
								ORDER BY distance";
								
	/**
	  * Extra query to get all closest station to a given point (lat/long)
	  * @param int offset
	  * @param int rowcount
	  */
	private $LIMIT_QUERY = " LIMIT :offset , :rowcount";

	/**
	  *
	  * @param int $offset Number of the first row to return (Optional)
	  * @param int $rowcount Number of rows to return (Optional)
	  * @return array A List of Stations in the given municipal
	  */
	public function getAllStations($offset=null, $rowcount=null) {
		$arguments = array(":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
		$query = $this->GET_ALL_STATIONS_QUERY;
		
		if($offset != null and $rowcount != null) {
			$query .= $this->LIMIT_QUERY;
		}

		$result = R::getAll($query, $arguments);
		
		$results = array();
		foreach($result as &$row){
			$station = array();
			$station["id"] = $row["stop_id"];
			$station["name"] = $row["stop_name"];
			$station["longitude"] = $row["stop_lat"];
			$station["latitude"] = $row["stop_lon"];
			
			$results[] = $station;
		}
		date_default_timezone_set("UTC");
		return $results;
	}

	/**
	  *
	  * @param string $municipal The longitude (Required)
	  * @param string $latitude The latitude (Required)
	  * @param int $offset Number of the first row to return (Optional)
	  * @param int $rowcount Number of rows to return (Optional)
	  * @return array A List of the closest stations to a given location 
	  */
	public function getClosestStations($longitude, $latitude, $offset=null, $rowcount=null) {
		$arguments = array(":latitude" => urldecode($latitude), ":longitude" => urldecode($longitude), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
		$query = $this->GET_CLOSEST_STATIONS_QUERY;
		
		if($offset != null and $rowcount != null) {
			$query .= $this->LIMIT_QUERY;
		}
		
		$result = R::getAll($query, $arguments);
		
		$results = array();
		foreach($result as &$row){
			$station = array();
			$station["id"] = $row["stop_id"];
			$station["name"] = $row["stop_name"];
			$station["longitude"] = $row["stop_lat"];
			$station["latitude"] = $row["stop_lon"];
			$station["distance"] = $row["distance"];
			
			$results[] = $station;
		}
		
		return $results;
	}
}