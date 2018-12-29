<?php
/**
 * DronePHP (http://www.dronephp.com)
 *
 * @link      http://github.com/Pleets/DronePHP
 * @copyright Copyright (c) 2016-2018 Pleets. (http://www.pleets.org)
 * @license   http://www.dronephp.com/license
 * @author    Darío Rivera <fermius.us@gmail.com>
 */

namespace DroneTest\FileSystem;

use Drone\FileSystem\Shell;
use PHPUnit\Framework\TestCase;

class ShellTest extends TestCase
{
    /**
     * Tests home path position
     *
     * @return null
     */
    public function testHomePath()
    {
        mkdir('foo');

        $shell = new Shell('foo');
        $this->assertSame('foo', $shell->getHome());
        $this->assertSame('foo', basename($shell->pwd()));
        $this->assertSame('foo', basename(getcwd()));
    }

    /**
     * Tests file creation
     *
     * @return null
     */
    public function testFileCreation()
    {
        $shell = new Shell('foo');
        $cmd = $shell->touch('new.txt');

        $this->assertTrue($cmd);
        $this->assertTrue(file_exists('new.txt'));
    }

    /**
     * Tests changing path
     *
     * @return null
     */
    public function testChangePath()
    {
        $shell = new Shell('foo');
        $shell->cd('..');

        $this->assertTrue(file_exists('foo'));
        $this->assertTrue(file_exists('foo/new.txt'));

        # back to home path
        $shell->cd();
        $this->assertSame('foo', basename($shell->pwd()));
        $this->assertSame('foo', basename(getcwd()));
    }

    /**
     * Tests simple copy
     *
     * @return null
     */
    public function testFileCopy()
    {
        $shell = new Shell('foo');
        $shell->cp('new.txt', 'new2.txt');

        $this->assertTrue(file_exists('new2.txt'));

        mkdir('bar');

        $shell->cp('new.txt', 'bar');
        $this->assertTrue(file_exists('bar/new.txt'));
    }

    /**
     * Tests directory creation
     *
     * @return null
     */
    public function testMakeDirectory()
    {
        $shell = new Shell('foo');
        $shell->mkdir('foo2');

        $this->assertTrue(file_exists('foo2'));
        $this->assertTrue(is_dir('foo2'));
    }

    /**
     * Tests the list of files retrived by ls command
     *
     * @return null
     */
    public function testListingFiles()
    {
        $shell = new Shell('foo');
        $files = $shell->ls();
        sort($files);

        $expected = ['bar', 'foo2', 'new.txt', 'new2.txt'];
        sort($expected);

        $this->assertSame($expected, $files);
    }

    /**
     * Tests the list of files retrived by ls command
     *
     * @return null
     */
    public function testListingFilesRecursively()
    {
        $shell = new Shell('foo');
        $files = $shell->ls('.', true);

        $files = array_values($files);
        sort($files);

        $expected = ['bar', 'foo2', 'new.txt', 'new2.txt', ['new.txt']];
        sort($expected);

        $this->assertSame($expected, $files);
    }

    /**
     * Tests copying a directory with its contents
     *
     * @return null
     */
    public function testDirectoryCopy()
    {
        $shell = new Shell('foo');

        $shell->touch('foo2/new3.txt');
        $shell->touch('foo2/new4.txt');

        $errorObject = null;

        try
        {
            $shell->cp('foo2', 'foo3');
        }
        catch (\Exception $e)
        {
            # omitting directory
            $errorObject = ($e instanceof \RuntimeException);
        }
        finally
        {
            $this->assertTrue($errorObject, $e->getMessage());
        }

        mkdir('foo3');

        # must be recursive
        $shell->cp(
            'foo2', // directory
            'foo3', // directory
        true);

        $this->assertTrue(file_exists('foo3'));
        $this->assertTrue(is_dir('foo3'));
        $this->assertTrue(file_exists('foo3/foo2'));
        $this->assertTrue(is_dir('foo3/foo2'));
        $this->assertTrue(file_exists('foo3/foo2/new3.txt'));
        $this->assertTrue(file_exists('foo3/foo2/new4.txt'));

        # for future improvement (copy a directory in a new directory)
        $shell->cp(
            'foo2', // directory
            'foo4', // not a directory or file
        true);
   }
}