<?php
/**
 * This is a class which will return all available Haltes from De Lijn
 *
 * @package packages/Haltes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Maarten Cautreels <maarten@flatturtle.com>
 */

include_once('DeLijnStationDao.php');

class DeLijnStations extends AReader
{

    /**
     * Class constructor
     * @param $package
     * @param $resource
     * @param $RESTparameters
     */
    public function __construct($package, $resource, $RESTparameters)
    {
        parent::__construct($package, $resource, $RESTparameters);

        // Initialize possible params
        $this->longitude = null;
        $this->latitude = null;
        $this->name = null;
        $this->id = null;
        $this->code = null;
        $this->offset = 0;
        $this->rowcount = 1024;
    }

    public static function getParameters()
    {
        return [
            "longitude" => "Longitude",
            "latitude"  => "Latitude",
            "name"      => "Name",
            "id"        => "Id",
            "offset"    => "Offeset",
            "code"      => "Haltenummber",
            "rowcount"  => "Rowcount"
        ];
    }

    public static function getRequiredParameters()
    {
        return [];
    }

    public function setParameter($key, $val)
    {
        if ($key == "longitude") {
            $this->longitude = $val;
        } elseif ($key == "latitude") {
            $this->latitude = $val;
        } elseif ($key == "name") {
            $this->name = $val;
        } elseif ($key == "offset") {
            $this->offset = $val;
        } elseif ($key == "rowcount") {
            $this->rowcount = $val;
        } elseif ($key == "id") {
            $this->id = $val;
        } elseif ($key == "code") {
            $this->code = $val;
        }
    }

    public function read()
    {
        $stationDao = new StationDao();

        if ($this->id != null) {
            return $stationDao->getStationById($this->id);
        } elseif ($this->code != null) {
            return $stationDao->getStationByCode($this->code);
        } elseif ($this->longitude != null && $this->latitude != null) {
            return $stationDao->getClosestStations($this->longitude, $this->latitude);
        } elseif ($this->name != null) {
            return $stationDao->getStationsByName($this->name, $this->offset, $this->rowcount);
        }

        return $stationDao->getAllStations($this->offset, $this->rowcount);
    }

    public static function getDoc()
    {
        return "This resource contains haltes from De Lijn.";
    }
}
