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

use Drone\Db\Driver\MySQL;
use Drone\Db\Driver\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class MySQLTest extends TestCase
{
    /**
     * Database parameters
     */
    private $options = [
        "dbhost"       => "localhost",
        "dbuser"       => "root",
        "dbpass"       => "",
        "dbname"       => "test",
        "dbchar"       => "utf8",
        "dbport"       => "3306",
        "auto_connect" => false
    ];

    /*
    |--------------------------------------------------------------------------
    | Stablishing connections
    |--------------------------------------------------------------------------
    |
    | The following tests are related to the connection methods and its
    | exceptions and returned values.
    |
    */

    /**
     * Tests if we can connect to the database server
     *
     * @return null
     */
    public function testCanStablishConnection()
    {
        $conn = new MySQL($this->options);

        $mysqliObject = $conn->connect();

        $this->assertInstanceOf('\mysqli', $mysqliObject);
        $this->assertTrue($conn->isConnected());
    }

    /**
     * Tests if we can disconnect from the database server
     *
     * @return null
     */
    public function testCanDownConnection()
    {
        $conn = new MySQL($this->options);

        $conn->connect();
        $result = $conn->disconnect();

        $this->assertNotTrue($conn->isConnected());
        $this->assertTrue($result);
    }

    /**
     * Tests if we can disconnect from server when there is not a connection stablished
     *
     * @return null
     */
    public function testCannotDisconectWhenNotConnected()
    {
        $conn = new MySQL($this->options);

        $errorObject = null;

        try {
            $conn->disconnect();
        }
        catch (\Exception $e)
        {
            $errorObject = ($e instanceof \LogicException);
        }
        finally
        {
            $this->assertNotTrue($conn->isConnected());
            $this->assertTrue($errorObject, $e->getMessage());
        }
    }

    /**
     * Tests if we can reconnect to the database server
     *
     * @return null
     */
    public function testCanStablishConnectionAgain()
    {
        $conn = new MySQL($this->options);

        $conn->connect();
        $mysqliObject = $conn->reconnect();

        $this->assertInstanceOf('\mysqli', $mysqliObject);
        $this->assertTrue($conn->isConnected());
    }

    /**
     * Tests if we can reconnect to the database server when there is not a connection stablished
     *
     * @return null
     */
    public function testCannotStablishReconnection()
    {
        $conn = new MySQL($this->options);

        $errorObject = null;

        try {
            $conn->reconnect();
        }
        catch (\Exception $e)
        {
            $errorObject = ($e instanceof \LogicException);
        }
        finally
        {
            $this->assertTrue($errorObject, $e->getMessage());
            $this->assertNotTrue($conn->isConnected());
        }
    }

    /**
     * Tests if a failed connection throws a ConnectionException
     *
     * @return null
     */
    public function testCannotStablishConnection()
    {
        $options = $this->options;
        $options["dbhost"] = 'myserver';   // this server does not exists

        $conn = new MySQL($options);

        $mysqliObject = $errorObject = null;

        $message = "No exception";

        try {
            $mysqliObject = $conn->connect();
        }
        catch (\Exception $e)
        {
            $errorObject = ($e instanceof ConnectionException);
            $message = $e->getMessage();
        }
        finally
        {
            $this->assertTrue($errorObject, $message);
            $this->assertNotTrue($conn->isConnected());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Quering and Transactions
    |--------------------------------------------------------------------------
    |
    | The following tests are related to query and transaction operations and its
    | exceptions and returned values.
    |
    */

    /**
     * Tests if we can execute DDL statements
     *
     * @return null
     */
    public function testCanExecuteDLLStatement()
    {
        $options = $this->options;
        $options["auto_connect"] = true;

        $conn = new MySQL($options);
        $sql = "CREATE TABLE MYTABLE (ID INTEGER(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, DESCRIPTION VARCHAR(100))";
        $result = $conn->execute($sql);

        $this->assertTrue(is_object($result));

        # properties modified by execute() method
        $this->assertEquals(0, $conn->getNumRows());
        $this->assertEquals(0, $conn->getNumFields());
        $this->assertEquals(0, $conn->getRowsAffected());
    }

    /**
     * Tests if we can execute DML statements
     *
     * @return null
     */
    public function testCanExecuteDMLStatement()
    {
        $options = $this->options;
        $options["auto_connect"] = true;

        $conn = new MySQL($options);
        $sql = "INSERT INTO MYTABLE (DESCRIPTION) VALUES ('Hello world!')";
        $result = $conn->execute($sql);

        $this->assertTrue(is_object($result));

        # properties modified by execute() method
        $this->assertEquals(0, $conn->getNumRows());
        $this->assertEquals(0, $conn->getNumFields());
        $this->assertEquals(1, $conn->getRowsAffected());
    }

    /**
     * Tests getting results
     *
     * @return null
     */
    public function testGettingResults()
    {
        $options = $this->options;
        $options["auto_connect"] = true;

        $conn = new MySQL($options);
        $sql = "SELECT * FROM MYTABLE LIMIT 2";
        $result = $conn->execute($sql);

        # properties modified by execute() method
        $this->assertEquals(1, $conn->getNumRows());
        $this->assertEquals(2, $conn->getNumFields());
        $this->assertEquals(0, $conn->getRowsAffected());

        $rowset = $conn->getArrayResult();    # array with results
        $row = array_shift($rowset);

        $this->assertArrayHasKey("ID", $row);
        $this->assertArrayHasKey("DESCRIPTION", $row);
    }

    /**
     * Tests if we can commit transactions
     *
     * @return null
     */
    public function testCommitBehavior()
    {
        $options = $this->options;
        $options["auto_connect"] = true;

        $conn = new MySQL($options);
        $conn->autocommit(false);

        $sql = "INSERT INTO MYTABLE (DESCRIPTION) VALUES ('COMMIT_ROW_1')";
        $result = $conn->execute($sql);

        $sql = "SELECT * FROM MYTABLE WHERE DESCRIPTION = 'COMMIT_ROW_1'";
        $result = $conn->execute($sql);
        $rowset = $conn->getArrayResult();

        $rowcount = count($row);

        $this->assertTrue(($rowcount === 0));

        # properties modified by execute() method
        $this->assertEquals(0, $conn->getNumRows());
        $this->assertEquals(0, $conn->getNumFields());
        $this->assertEquals(0, $conn->getRowsAffected());

        $conn->commit();
        $this->assertTrue(($rowcount === 0));

        # properties modified by execute() method
        $this->assertEquals(0, $conn->getNumRows());
        $this->assertEquals(0, $conn->getNumFields());
        $this->assertEquals(1, $conn->getRowsAffected());
    }
}