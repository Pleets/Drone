<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016 DronePHP. (http://www.dronephp.com)
 * @license   http://www.dronephp.com/license
 */

namespace Drone\Db;

use Drone\Db\Entity;
use Drone\Sql\Platform\SQLFunction;

class TableGateway extends AbstractTableGateway implements TableGatewayInterface
{
    /**
     * Entity instance
     *
     * @var Entity
     */
    private $entity;

    /**
     * Constructor
     *
     * @param Entity $entity
     *
     * @return null
     */
    public function __construct(Entity $entity, $auto_connect = true)
    {
        parent::__construct("default", $auto_connect);
        $this->entity = $entity;
    }

    /**
     * Returns the entity
     *
     * @return Drone_Db_Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Select statement
     *
     * @param array $where
     *
     * @return array With all results
     */
    public function select($where = [])
    {
        if (count($where))
        {
            $parsed_where = [];

            foreach ($where as $key => $value)
            {
                if (is_string($value))
                    $parsed_where[] = "$key = '$value'";
                elseif ($value instanceof SQLFunction)
                    $parsed_where[] = "$key = " . $value->getStatement();
                else
                    $parsed_where[] = "$key = $value";
            }

            $where = "WHERE \r\n\t" . implode(" AND\r\n\t", $parsed_where);
        }
        else
            $where = "";

        $table = $this->entity->getTableName();

        $sql = "SELECT * \r\nFROM {$table}\r\n$where";

        $result = $this->getDb()->query($sql);
        return $this->getDb()->getArrayResult();
    }

    /**
     * Insert statement
     *
     * @param array $data
     *
     * @return boolean
     */
    public function insert($data)
    {
        if (!count($data))
            throw new Exception("Missing values for INSERT statement!");

        foreach ($data as $key => $value)
        {
            if (is_string($value))
                $value = "'$value'";
            elseif (is_null($value))
                $value = "null";
            elseif ($value instanceof SQLFunction)
                $value = $value->getStatement();

            $data[$key] = $value;
        }

        $cols = implode(",\r\n\t", array_keys($data));
        $vals = implode(",\r\n\t", array_values($data));

        $table = $this->entity->getTableName();

        $sql = "INSERT INTO {$table} \r\n(\r\n\t$cols\r\n) \r\nVALUES \r\n(\r\n\t$vals\r\n)";

        return $this->getDb()->query($sql);
    }

    /**
     * Update statement
     *
     * @param array $set
     * @param array $where
     *
     * @return boolean
     */
    public function update($set, $where)
    {
        $parsed_set = array();

        if (!count($set))
            throw new Exception("Missing SET arguments!");

        foreach ($set as $key => $value)
        {
            if (is_string($value))
                $value = "'$value'";
            elseif (is_null($value))
                $value = "null";
            elseif ($value instanceof SQLFunction)
                $value = $value->getStatement();

            $parsed_set[] = "$key = $value";
        }

        $parsed_set = implode(",\r\n\t", $parsed_set);


        $parsed_where = array();

        foreach ($where as $key => $value)
        {
            if (is_string($value))
                $parsed_where[] = "$key = '$value'";
            elseif ($value instanceof SQLFunction)
                $parsed_where[] = "$key = " . $value->getStatement();
            else
                $parsed_where[] = "$key = $value";
        }

        $parsed_where = implode(" AND\r\n\t", $parsed_where);

        $table = $this->entity->getTableName();

        $sql = "UPDATE {$table} \r\nSET \r\n\t$parsed_set \r\nWHERE \r\n\t$parsed_where";

        return $this->getDb()->query($sql);
    }

    /**
     * Delete statement
     *
     * @param array $where
     *
     * @return boolean
     */
    public function delete($where)
    {
        if (count($where))
        {
            $parsed_where = [];

            foreach ($where as $key => $value)
            {
                if (is_string($value))
                    $parsed_where[] = "$key = '$value'";
                elseif ($value instanceof SQLFunction)
                    $parsed_where[] = "$key = " . $value->getStatement();
                else
                    $parsed_where[] = "$key = $value";
            }

            $where = "\r\nWHERE \r\n\t" . implode(" AND\r\n\t", $parsed_where);
        }
        else
            throw new Exception("You cannot delete rows without WHERE clause!. Use TRUNCATE statement instead.");

        $table = $this->entity->getTableName();

        $sql = "DELETE FROM {$table} $where";

        return $this->getDb()->query($sql);
    }
}