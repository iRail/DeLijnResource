<?php

/**
 * This is a class which will return the information with the latest departures from a certain station
 * 
 * @package packages/StopTimesDao
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */
class StopTimesDao {
	/*
	 *	Timezone set to Europe/Brussels
	 */
	var $timezone = "Europe/Brussels";

	/**
	  * Query to get all stations ordered alphabetically
	  * @param int stationId
	  */
	private $GET_DEPARTURES_QUERY = "SELECT DISTINCT route.route_short_name, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.departure_time_t
									FROM dlgtfs_stop_times times
									JOIN dlgtfs_trips trip
										ON trip.trip_id = times.trip_id
									JOIN dlgtfs_routes route
										ON route.route_id = trip.route_id
									JOIN dlgtfs_calendar_dates calendar
										ON calendar.service_id = trip.service_id
									INNER JOIN dlgtfs_stops stop
										ON stop.stop_name = :stationName
									WHERE times.stop_id = stop.stop_id
									AND times.departure_time_t >= TIME(STR_TO_DATE(CONCAT(:hour, ':', :minute), '%k:%i'))
									AND calendar.date <= STR_TO_DATE(CONCAT(:year, '-', :month, '-', :day), '%Y-%m-%d')
									ORDER BY times.departure_time_t
									LIMIT :offset, :rowcount;";
									
	private $GET_ARRIVALS_QUERY = "SELECT DISTINCT route.route_short_name, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.arrival_time_t
									FROM dlgtfs_stop_times times
									JOIN dlgtfs_trips trip
										ON trip.trip_id = times.trip_id
									JOIN dlgtfs_routes route
										ON route.route_id = trip.route_id
									JOIN dlgtfs_calendar_dates calendar
										ON calendar.service_id = trip.service_id
									INNER JOIN dlgtfs_stops stop
										ON stop.stop_name = :stationName
									WHERE times.stop_id = stop.stop_id
									AND times.arrival_time_t >= TIME(STR_TO_DATE(CONCAT(:hour, ':', :minute), '%k:%i'))
									AND calendar.date <= STR_TO_DATE(CONCAT(:year, '-', :month, '-', :day), '%Y-%m-%d')
									ORDER BY times.arrival_time_t
									LIMIT :offset, :rowcount;";
																
	/**
	  *
	  * @param int $stationName The Name of a station (Required)
	  * @param int $year The Year (Required)
	  * @param int $month The Month (Required)
	  * @param int $day The Day (Required)
	  * @param int $hour The Hour (Required)
	  * @param int $minute The Minute (Required)
	  * @return array A List of Departures for a given station, date and starting from a given time
	  */
	public function getDepartures($stationName, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
		date_default_timezone_set($this->timezone);
		
		$arguments = array(":stationName" => urldecode($stationName), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
		$query = $this->GET_DEPARTURES_QUERY;
		
		
		$result = R::getAll($query, $arguments);
		
		$departures = array();
		foreach($result as &$row){
			$departure = array();
			
			$departure["short_name"] = $row["route_short_name"];
			$departure["long_name"] = $row["trip_headsign"];
			$departure["color"] = $row["route_color"];
			$departure["text_color"] = $row["route_text_color"];
			$departure["direction"] = $row["direction_id"];

			$split = explode(':', $row["departure_time_t"]);
			$hour = $split[0];
			$minute = $split[1];
			
			$date = mktime($hour, $minute, 0, $month, $day, $year);
			$departure["iso8601"] = date("c", $date);
			$departure["time"] = date("U", $date);
			
			$departures[] = $departure;
		}

		return $departures;
	}
	
	/**
	  *
	  * @param int $stationId The Unique identifier of a station (Required)
	  * @param int $year The Year (Required)
	  * @param int $month The Month (Required)
	  * @param int $day The Day (Required)
	  * @param int $hour The Hour (Required)
	  * @param int $minute The Minute (Required)
	  * @return array A List of Arrivals for a given station, date and starting from a given time
	  */
	public function getArrivals($stationName, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
		date_default_timezone_set($this->timezone);
		
		$arguments = array(":stationName" => urldecode($stationName), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
		$query = $this->GET_ARRIVALS_QUERY;
		
		$result = R::getAll($query, $arguments);
		
		$arrivals = array();
		foreach($result as &$row){
			$arrival = array();
			
			$arrival["short_name"] = $row["route_short_name"];
			$arrival["long_name"] = $row["trip_headsign"];
			$arrival["color"] = $row["route_color"];
			$arrival["text_color"] = $row["route_text_color"];
			$arrival["direction"] = $row["direction_id"];

			$split = explode(':', $row["arrival_time_t"]);
			$hour = $split[0];
			$minute = $split[1];
			
			$date = mktime($hour, $minute, 0, $month, $day, $year);
			$arrival["iso8601"] = date("c", $date);
			$arrival["time"] = date("U", $date);
			
			$arrivals[] = $arrival;
		}
		
		return $arrivals;
	}
}