<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016-2017 Pleets. (http://www.pleets.org)
 * @license   http://www.dronephp.com/license
 */

namespace Drone\Db\TableGateway;

interface TableGatewayInterface
{
   public function select(Array $where);
   public function insert(Array $data);
   public function update(Array $set, Array $where);
   public function delete(Array $where);
}