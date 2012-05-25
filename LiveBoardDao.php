<?php

/**
 * This is a class which will return the information with the latest departures from a certain station
 * 
 * @package packages/LiveBoard
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */
class LiveboardDao {
	/**
	  * Query to get all stations ordered alphabetically
	  * @param int stationId
	  */
	private $GET_LIVEBOARD_QUERY = "SELECT DISTINCT route.route_short_name, route.route_long_name, route.route_color, times.departure_time_t
									FROM dlgtfs_stop_times times
									JOIN dlgtfs_trips trip
										ON trip.trip_id = times.trip_id
									JOIN dlgtfs_routes route
										ON route.route_id = trip.route_id
									JOIN dlgtfs_calendar_dates calendar
										ON calendar.service_id = trip.service_id
									WHERE times.stop_id = :stationid
									AND times.departure_time_t >= CURTIME()
									AND calendar.date <= CURDATE()
									ORDER BY times.departure_time_t";
								
	/**
	  * Extra query to get all closest station to a given point (lat/long)
	  * @param int offset
	  * @param int rowcount
	  */
	private $LIMIT_QUERY = " LIMIT :offset , :rowcount";
								
	/**
	  *
	  * @param int $stationId The Unique identifier of a station (Required)
	  * @param int $offset Number of the first row to return (Optional)
	  * @param int $rowcount Number of rows to return (Optional)
	  * @return array A List of Stations in the given municipal
	  */
	public function getLiveBoard($stationId, $offset=null, $rowcount=null) {
		$arguments = array(":stationid" => intval(urldecode($stationId)), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
		$query = $this->GET_LIVEBOARD_QUERY;
		
		if($offset != null and $rowcount != null) {
			$query .= $this->LIMIT_QUERY;
		}

		$result = R::getAll($query, $arguments);
		
		$results = array();
		foreach($result as &$row){
			$departure = array();
			
			$departure["route_short_name"] = $row["route_short_name"];
			$departure["route_long_name"] = $row["route_long_name"];
			$departure["route_color"] = $row["route_color"];
			$departure["departure_time"] = $row["departure_time_t"];
			
			
			$results[] = $departure;
		}
		date_default_timezone_set("UTC");
		return $results;
	}
}