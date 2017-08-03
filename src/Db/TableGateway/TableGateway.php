<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016-2017 Pleets. (http://www.pleets.org)
 * @license   http://www.dronephp.com/license
 */

namespace Drone\Db\TableGateway;

use Drone\Db\Entity;
use Drone\Db\SQLFunction;
use Exception;

class TableGateway extends AbstractTableGateway implements TableGatewayInterface
{
    /**
     * Entity instance
     *
     * @var Entity
     */
    private $entity;

    /**
     * Returns the entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Constructor
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity, $auto_connect = true)
    {
        parent::__construct($entity->getConnectionIdentifier(), $auto_connect);
        $this->entity = $entity;
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
        $bind_values = [];

        if (count($where))
        {
            $parsed_where = [];

            $k = 0;

            foreach ($where as $key => $value)
            {
                $k++;

                if (is_null($value))
                    $parsed_where[] = "$key IS NULL";
                elseif ($value instanceof SQLFunction)
                    $parsed_where[] = "$key = " . $value->getStatement();
                elseif (is_array($value))
                {
                    $parsed_in = [];

                    foreach ($value as $in_value)
                    {
                        $parsed_in[] = ":$k";
                        $bind_values[":$k"] = $in_value;

                        $k++;
                    }

                    $parsed_where[] = "$key IN (" . implode(", ", $parsed_in) . ")";
                }
                else
                {
                    $parsed_where[] = "$key = :$k";
                    $bind_values[":$k"] = $value;
                }
            }

            $where = "WHERE \r\n\t" . implode(" AND\r\n\t", $parsed_where);
        }
        else
            $where = "";

        $table = $this->entity->getTableName();

        $sql = "SELECT * \r\nFROM {$table}\r\n$where";

        $result = (count($bind_values)) ? $this->getDriver()->getDb()->execute($sql, $bind_values) : $this->getDriver()->getDb()->execute($sql);

        return $this->getDriver()->getDb()->getArrayResult();
    }

    /**
     * Insert statement
     *
     * @param array $data
     *
     * @throws Exception
     * @return boolean
     */
    public function insert($data)
    {
        if (!count($data))
            throw new Exception("Missing values for INSERT statement!");

        $bind_values = [];

        $k = 0;

        foreach ($data as $key => $value)
        {
            $k++;

            if (is_null($value))
                $value = "NULL";
            elseif ($value instanceof SQLFunction)
                $value = $value->getStatement();
            else {
                $bind_values[":$k"] = $value;
                $value = ":$k";
            }

            $data[$key] = $value;
        }

        $cols = implode(",\r\n\t", array_keys($data));
        $vals = implode(",\r\n\t", array_values($data));

        $table = $this->entity->getTableName();

        $sql = "INSERT INTO {$table} \r\n(\r\n\t$cols\r\n) \r\nVALUES \r\n(\r\n\t$vals\r\n)";

        return $this->getDriver()->getDb()->execute($sql, $bind_values);
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
        $parsed_set = [];

        if (!count($set))
            throw new Exception("Missing SET arguments!");

        $bind_values = [];

        $k = 0;

        foreach ($set as $key => $value)
        {
            $k++;

            if (is_null($value))
                $parsed_set[] = "$key = NULL";
            elseif ($value instanceof SQLFunction)
                $parsed_set[] = "$key = " . $value->getStatement();
            elseif (is_array($value))
            {
                $parsed_in = [];

                foreach ($value as $in_value)
                {
                    if (is_string($in_value))
                        $parsed_in[] = ":$k";

                    $bind_values[":$k"] = $in_value;

                    $k++;
                }

                $parsed_set[] = "$key IN (" . implode(", ", $parsed_in) . ")";
            }
            else
            {
                $parsed_set[] = "$key = :$k";
                $bind_values[":$k"] = $value;
            }
        }

        $parsed_set_array = $parsed_set;
        $parsed_set = implode(",\r\n\t", $parsed_set);

        $parsed_where = [];

        foreach ($where as $key => $value)
        {
            $k++;

            if (is_null($value))
                $parsed_where[] = "$key IS NULL";
            elseif ($value instanceof SQLFunction)
                $parsed_where[] = "$key = " . $value->getStatement();
            elseif (is_array($value))
            {
                $parsed_in = [];

                foreach ($value as $in_value)
                {
                    $parsed_in[] = ":$k";
                    $bind_values[":$k"] = $in_value;

                    $k++;
                }

                $parsed_where[] = "$key IN (" . implode(", ", $parsed_in) . ")";
            }
            else
            {
                $parsed_where[] = "$key = :$k";
                $bind_values[":$k"] = $value;
            }
        }

        $parsed_where = implode(" AND\r\n\t", $parsed_where);

        $table = $this->entity->getTableName();

        $sql = "UPDATE {$table} \r\nSET \r\n\t$parsed_set \r\nWHERE \r\n\t$parsed_where";

        return $this->getDriver()->getDb()->execute($sql, $bind_values);
    }

    /**
     * Delete statement
     *
     * @param array $where
     *
     * @throws Exception
     * @return boolean
     */
    public function delete($where)
    {
        if (count($where))
        {
            $parsed_where = [];

            $bind_values = [];

            $k = 0;

            foreach ($where as $key => $value)
            {
                $k++;

                if (is_null($value))
                    $parsed_where[] = "$key IS NULL";
                elseif ($value instanceof SQLFunction)
                    $parsed_where[] = "$key = " . $value->getStatement();
                elseif (is_array($value))
                {
                    $parsed_in = [];

                    foreach ($value as $in_value)
                    {
                        $parsed_in[] = ":$k";
                        $bind_values[":$k"] = $value;

                        $k++;
                    }

                    $parsed_where[] = "$key IN (" . implode(", ", $parsed_in) . ")";
                }
                else
                {
                    $parsed_where[] = "$key = :$k";
                    $bind_values[":$k"] = $value;
                }
            }

            $where = "\r\nWHERE \r\n\t" . implode(" AND\r\n\t", $parsed_where);
        }
        else
            throw new Exception("You cannot delete rows without WHERE clause!. Use TRUNCATE statement instead.");

        $table = $this->entity->getTableName();

        $sql = "DELETE FROM {$table} $where";

        return $this->getDriver()->getDb()->execute($sql, $bind_values);
    }
}