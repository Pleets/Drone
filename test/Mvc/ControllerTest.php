<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016-2018 Pleets. (http://www.pleets.org)
 * @license   http://www.dronephp.com/license
 * @author    Darío Rivera <fermius.us@gmail.com>
 */

namespace DroneTest\Util;

use Drone\Mvc\AbstractController;
use Drone\Mvc\Exception\MethodNotFoundException;
use Drone\Mvc\Exception\PrivateMethodExecutionException;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    /**
     * Tests controller execution
     *
     * @return null
     */
    public function testInstantiationAndMethodExecution()
    {
        /*
         * Explicit method execution
         */

        $ctrl = new \App\Controller\Home;
        $result = $ctrl->about();

        $expected = ["greeting" => "Hello World!"];
        $this->assertSame($expected, $result);

        /*
         * Implicit method execution
         */

        $ctrl = new \App\Controller\Home;
        $ctrl->setMethod('about');
        $result = $ctrl->execute();

        $expected = ["greeting" => "Hello World!"];
        $this->assertSame($expected, $result);
    }

    /**
     * Tests if we can execute the controller on private methods
     *
     * @return null
     */
    public function testExecutingWhenMethodIsPrivate()
    {
        $ctrl = new \App\Controller\Home;

        $errorObject = null;

        try {
            $ctrl->setMethod('doSomething');
            $ctrl->execute();
        } catch (\Exception $e) {
            $errorObject = ($e instanceof PrivateMethodExecutionException);
        } finally {
            $this->assertTrue($errorObject, $e->getMessage());
        }
    }

    /**
     * Tests if we can execute the controller on non-existing methods
     *
     * @return null
     */
    public function testExecutingWhenMethodDoesNotExists()
    {
        $ctrl = new \App\Controller\Home;

        $errorObject = null;

        try {
            $ctrl->setMethod('notFound');
            $ctrl->execute();
        } catch (\Exception $e) {
            $errorObject = ($e instanceof MethodNotFoundException);
        } finally {
            $this->assertTrue($errorObject, $e->getMessage());
        }
    }
}

/*
|--------------------------------------------------------------------------
| Controller class
|--------------------------------------------------------------------------
|
| This is a simple controller implementing AbstractController.
|
*/

namespace App\Controller;

use Drone\Mvc\AbstractController;

class Home extends AbstractController
{
    public function about()
    {
        return ["greeting" => "Hello World!"];
    }

    private function doSomething()
    {
        return ["result" => "45dEf7f8EF"];
    }
}
