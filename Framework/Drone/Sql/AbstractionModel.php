<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016 DronePHP. (http://www.dronephp.com)
 * @license   http://www.dronephp.com/license
 */

namespace Drone\Sql;

abstract class AbstractionModel
{
    /**
     * Driver identifier
     *
     * @var string
     */
    private $driver;

    /**
     * Connection resource
     *
     * @var resource
     */
    private $db;

    /**
     * All supported drivers
     *
     * @var array
     */
    private $availableDrivers;

    /**
     * Constructor
     *
     * @param string  $abstract_connection_string
     * @param boolean $auto_connect
     */
    public function __construct($abstract_connection_string = "default", $auto_connect = true)
    {
		$dbsettings = include(__DIR__ . "/../../../config/database.config.php");

        # driver => className
        $this->availableDrivers = array(
            "Oci8"          => "Drone\Sql\Oracle",
            "Mysqli"        => "Drone\Sql\MySQL",
            "Sqlsrv"        => "Drone\Sql\SQLServer",
        );

        $drv = $dbsettings[$abstract_connection_string]["driver"];
        $dbsettings[$abstract_connection_string]["auto_connect"] = $auto_connect;

        if (array_key_exists($drv, $this->availableDrivers))
        {
            $driver = $this->getAvailableDrivers();

            $this->db = new $driver[$drv]($dbsettings[$abstract_connection_string]);
        }
        else
            throw new Exception("The Database driver does not exists");
	}

    /**
     * Returns the current driver
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Returns the current connection resource
     *
     * @return resource
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns all supported drivers
     *
     * @return array
     */
    public function getAvailableDrivers()
    {
        return $this->availableDrivers;
    }
}