<?php
/**
 * Alfred Workflow Redmine
 *
 * Open a Redmine project page
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */

namespace AlfredWorkflow\Tests;

use Alfred\Workflow;
use AlfredWorkflow\Redmine\Storage\Cache;
use AlfredWorkflow\Redmine\Storage\Settings;
use AlfredWorkflow\Redmine;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * Test class for AlfredWorkflow\Redmine class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class RedmineTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir      = '';
    protected $tmpCacheDir = '';
    protected $bundleId    = 'test';

    public function setUp()
    {
        defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
        $this->tmpDir      = __DIR__ . '/../../tmp/';
        $this->tmpCacheDir = $this->tmpDir . 'cache/';
        if (!file_exists($this->tmpCacheDir)) {
            mkdir($this->tmpCacheDir, 0755, true);
        }
    }

    /**
     * Data provider for runExceptionTest method
     *
     * @return array
     */
    public function runExceptionTestDataProvider()
    {
        return array(
            array('test', ''),
        );
    }

    /**
     * Test exceptions for run method of AlfredWorkflow\Redmine class
     *
     * @covers       AlfredWorkflow\Redmine
     * @dataProvider runExceptionTestDataProvider
     * @test
     */
    public function runExceptionTest($actionGroup, $query)
    {
        $redmine = new Redmine(new Settings($this->bundleId), new Workflow(), new Cache($this->bundleId, $this->tmpDir . '/cache'));
        $result  = $redmine->run($actionGroup, $query);

        $this->assertContains('An error occured', $result);
    }

    /**
     * Data provider for logTest method
     *
     * @return array
     */
    public function logTestDataProvider()
    {
        return array(
            array(true,  Logger::DEBUG,   true),
            array(false, Logger::DEBUG,   false),
            array(true,  Logger::INFO,    true),
            array(false, Logger::INFO,    false),
            array(true,  Logger::WARNING, true),
            array(false, Logger::WARNING, true),
            array(true,  Logger::ERROR,   true),
            array(false, Logger::ERROR,   true),
        );
    }

    /**
     * Test exceptions for log method of AlfredWorkflow\Redmine class
     *
     * @covers       AlfredWorkflow\Redmine
     * @dataProvider logTestDataProvider
     * @test
     */
    public function logTest($debug, $msgLevel, $expectedResult)
    {
        $testMsg  = 'Test message';
        $handler  = $this->initLoggerHandler($debug);
        $redmine  = new Redmine(new Settings($this->bundleId), new Workflow(), new Cache($this->bundleId, $this->tmpCacheDir));
        Redmine::setDebug($debug);
        $redmine->setLoggerHandler($handler);
        $redmine->log($testMsg, $msgLevel);

        switch ($msgLevel) {
            case Logger::DEBUG:
                $this->assertEquals($expectedResult, $handler->hasDebug($testMsg, $msgLevel));
                break;
            case Logger::INFO:
                $this->assertEquals($expectedResult, $handler->hasInfo($testMsg, $msgLevel));
                break;
            case Logger::WARNING:
                $this->assertEquals($expectedResult, $handler->hasWarning($testMsg, $msgLevel));
                break;
            case Logger::ERROR:
                $this->assertEquals($expectedResult, $handler->hasError($testMsg, $msgLevel));
                break;
        }
    }

    /**
     * Instanciate and configure a logger handler for test purpose
     *
     * @param boolean $debug debug mode
     *
     * @return TestHandler
     */
    protected function initLoggerHandler($debug)
    {
        $level  = Logger::WARNING;
        if ($debug) {
            $level = Logger::DEBUG;
        }

        return new TestHandler($level);
    }

    public function tearDown()
    {
        exec('rm -rf ' . $this->tmpDir);
    }
}
