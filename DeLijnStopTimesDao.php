<?php

/**
 * This is a class which will return the information with the latest departures from a certain station
 * 
 * @package packages/StopTimesDao
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */
 
include_once('custom/packages/DeLijn/Config.class.php');
 
class StopTimesDao {

    public function __construct() {
        R::setup(DeLijnConfig::$DB, DeLijnConfig::$DB_USER, DeLijnConfig::$DB_PASSWORD);
    }

    /*
     *	Timezone set to Europe/Brussels
     */
    var $timezone = "Europe/Brussels";

    private $GET_DEPARTURES_BY_NAME_QUERY = "SELECT DISTINCT route.route_short_name, route.type, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.departure_time_t
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
									AND (SELECT count(time.stop_id) 
										FROM  dlgtfs_stop_times time
										WHERE time.trip_id = trip.trip_id
										AND time.arrival_time_t > times.departure_time_t) > 0
									ORDER BY times.departure_time_t
									LIMIT :offset, :rowcount;";
									
    private $GET_DEPARTURES_BY_ID_QUERY = "SELECT DISTINCT route.route_short_name, route.type, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.departure_time_t
									FROM dlgtfs_stop_times times
									JOIN dlgtfs_trips trip
										ON trip.trip_id = times.trip_id
									JOIN dlgtfs_routes route
										ON route.route_id = trip.route_id
									JOIN dlgtfs_calendar_dates calendar
										ON calendar.service_id = trip.service_id
									WHERE times.stop_id = :stationId
									AND times.departure_time_t >= TIME(STR_TO_DATE(CONCAT(:hour, ':', :minute), '%k:%i'))
									AND calendar.date <= STR_TO_DATE(CONCAT(:year, '-', :month, '-', :day), '%Y-%m-%d')
									AND (SELECT count(time.stop_id) 
										FROM  dlgtfs_stop_times time
										WHERE time.trip_id = trip.trip_id
										AND time.arrival_time_t > times.departure_time_t) > 0
									ORDER BY times.departure_time_t
									LIMIT :offset, :rowcount;";
									
    private $GET_ARRIVALS_BY_NAME_QUERY = "SELECT DISTINCT route.route_short_name, route.type, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.arrival_time_t
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
									AND (SELECT count(time.stop_id) 
										FROM  dlgtfs_stop_times time
										WHERE time.trip_id = trip.trip_id
										AND time.arrival_time_t > times.departure_time_t) > 0
									ORDER BY times.arrival_time_t
									LIMIT :offset, :rowcount;";
									
    private $GET_ARRIVALS_BY_ID_QUERY = "SELECT DISTINCT route.route_short_name, route.type, trip.trip_headsign, route.route_color, route.route_text_color, trip.direction_id, times.arrival_time_t
									FROM dlgtfs_stop_times times
									JOIN dlgtfs_trips trip
										ON trip.trip_id = times.trip_id
									JOIN dlgtfs_routes route
										ON route.route_id = trip.route_id
									JOIN dlgtfs_calendar_dates calendar
										ON calendar.service_id = trip.service_id
									WHERE times.stop_id = :stationId
									AND times.arrival_time_t >= TIME(STR_TO_DATE(CONCAT(:hour, ':', :minute), '%k:%i'))
									AND calendar.date <= STR_TO_DATE(CONCAT(:year, '-', :month, '-', :day), '%Y-%m-%d')
									AND (SELECT count(time.stop_id) 
										FROM  dlgtfs_stop_times time
										WHERE time.trip_id = trip.trip_id
										AND time.arrival_time_t > times.departure_time_t) > 0
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
    public function getDeparturesByName($stationName, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
        date_default_timezone_set($this->timezone);
		
        $arguments = array(":stationName" => urldecode($stationName), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
        $query = $this->GET_DEPARTURES_BY_NAME_QUERY;
		
        $result = R::getAll($query, $arguments);
        return $this->parseStopTimes($result, $year, $month, $day);
    }
	
    /**
     *
     * @param int $stationId The ID of a station (Required)
     * @param int $year The Year (Required)
     * @param int $month The Month (Required)
     * @param int $day The Day (Required)
     * @param int $hour The Hour (Required)
     * @param int $minute The Minute (Required)
     * @return array A List of Departures for a given station, date and starting from a given time
     */
    public function getDeparturesByID($stationId, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
        date_default_timezone_set($this->timezone);
		
        $arguments = array(":stationId" => urldecode($stationId), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
        $query = $this->GET_DEPARTURES_BY_ID_QUERY;
		
        $result = R::getAll($query, $arguments);
        return $this->parseStopTimes($result, $year, $month, $day);
    }
	
    /**
     *
     * @param int $stationName The Name of a station (Required)
     * @param int $year The Year (Required)
     * @param int $month The Month (Required)
     * @param int $day The Day (Required)
     * @param int $hour The Hour (Required)
     * @param int $minute The Minute (Required)
     * @return array A List of Arrivals for a given station, date and starting from a given time
     */
    public function getArrivalsByName($stationName, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
        date_default_timezone_set($this->timezone);
		
        $arguments = array(":stationName" => urldecode($stationName), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
        $query = $this->GET_ARRIVALS_BY_NAME_QUERY;
		
        $result = R::getAll($query, $arguments);
        return $this->parseStopTimes($result, $year, $month, $day);
    }
	
    /**
     *
     * @param int $stationId The ID of a station (Required)
     * @param int $year The Year (Required)
     * @param int $month The Month (Required)
     * @param int $day The Day (Required)
     * @param int $hour The Hour (Required)
     * @param int $minute The Minute (Required)
     * @return array A List of Arrivals for a given station, date and starting from a given time
     */
    public function getArrivalsById($stationId, $year, $month, $day, $hour, $minute, $offset=0, $rowcount=1024) {	
        date_default_timezone_set($this->timezone);
		
        $arguments = array(":stationId" => urldecode($stationId), ":year" => urldecode($year), ":month" => urldecode($month), ":day" => urldecode($day), ":hour" => urldecode($hour), ":minute" => urldecode($minute), ":offset" => intval(urldecode($offset)), ":rowcount" => intval(urldecode($rowcount)));
        $query = $this->GET_ARRIVALS_BY_ID_QUERY;
		
        $result = R::getAll($query, $arguments);
        return $this->parseStopTimes($result, $year, $month, $day);
    }
	
    /**
     * Parses the data that comes out of the queries and creates nice objects that could be returned to the user.
     */
    private function parseStopTimes($result, $year, $month, $day) {
        $stoptimes = array();
        foreach($result as &$row){
            $stoptime = array();
			
            $stoptime["short_name"] = $row["route_short_name"];
            $stoptime["type"] = $row["route_type"];
            $stoptime["long_name"] = $row["trip_headsign"];
            $stoptime["color"] = $row["route_color"];
            $stoptime["text_color"] = $row["route_text_color"];
            $stoptime["direction"] = $row["direction_id"];

            if(isset($row["departure_time_t"])) {
                $timeString =  $row["departure_time_t"];
            } else {
                $timeString =  $row["arrival_time_t"];
            }
			
            $split = explode(':', $timeString);
            $hour = $split[0];
            $minute = $split[1];
			
            $date = mktime($hour, $minute, 0, $month, $day, $year);
            $stoptime["iso8601"] = date("c", $date);
            $stoptime["time"] = date("U", $date);
			
            $stoptimes[] = $stoptime;
        }
		
        return $stoptimes;
    }
}