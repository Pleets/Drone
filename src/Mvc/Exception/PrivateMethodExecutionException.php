<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016-2018 Pleets. (http://www.pleets.org)
 * @license   http://www.dronephp.com/license
 * @author    Darío Rivera <fermius.us@gmail.com>
 */

namespace Drone\Mvc\Exception;

/**
 * PrivateMethodExecutionException Class
 *
 * This exception is thrown when the method to execute is not public
 */
class PrivateMethodExecutionException extends PageNotFoundException
{
}
