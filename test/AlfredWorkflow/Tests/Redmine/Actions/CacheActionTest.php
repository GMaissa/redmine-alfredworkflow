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

namespace AlfredWorkflow\Tests\Redmine\Actions;

use Alfred\Workflow;
use AlfredWorkflow\Redmine;
use AlfredWorkflow\Redmine\Storage\Settings;
use AlfredWorkflow\Redmine\Storage\Cache;

/**
 * Test class for AlfredWorkflow\Redmine\Actions\CacheAction class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class CacheActionTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir        = '';
    protected $tmpCacheDir   = '';
    protected $bundleId      = 'test';
    const TEST_ASSETS_PATH   = '/../../../../data/';

    public function setUp()
    {
        defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
        $this->tmpDir      = __DIR__ . '/../../../../tmp/';
        $this->tmpCacheDir = $this->tmpDir . 'cache/';
        if (!file_exists($this->tmpCacheDir)) {
            mkdir($this->tmpCacheDir, 0755, true);
        }
    }

    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function runTestDataProvider()
    {
        $configEmpty = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono  = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configMulti = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers/';
        $cacheEmpty  = __DIR__ . self::TEST_ASSETS_PATH . 'cache/empty/';
        $cacheMono   = __DIR__ . self::TEST_ASSETS_PATH . 'cache/mono-server/';
        $cacheMulti  = __DIR__ . self::TEST_ASSETS_PATH . 'cache/multi-servers/';
        $allActions  = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/cache/all-actions.xml');
        return array(
            array($configEmpty, '',            $cacheEmpty, $allActions),
            array($configMono,  '',            $cacheMono,  $allActions),
            array($configMulti, '',            $cacheMulti, $allActions),
            array($configEmpty, ' ',           $cacheEmpty, $allActions),
            array($configMono,  ' ',           $cacheMono,  $allActions),
            array($configMulti, ' ',           $cacheMulti, $allActions),
            array($configEmpty, 'clear',       $cacheEmpty, $allActions),
            array($configMono,  'clear',       $cacheMono,  $allActions),
            array($configMulti, 'clear',       $cacheMulti, $allActions),
            array($configEmpty, 'clear-cache', $cacheEmpty, $allActions),
            array($configMono,  'clear-cache', $cacheMono,  $allActions),
            array($configMulti, 'clear-cache', $cacheMulti, $allActions),
        );
    }

    /**
     * Test run method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Cache
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\CacheAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider runTestDataProvider
     * @test
     */
    public function testRun($config, $input, $cacheDir, $expectedResultReturn)
    {
        Cache::setDataDuration(10);
        $redmine = new Redmine(new Settings($this->bundleId, $config), new Workflow(), new Cache($this->bundleId, $cacheDir));
        $result  = $redmine->run('cache', $input);

        $this->assertEquals($expectedResultReturn, $result);
    }

    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function saveTestDataProvider()
    {
        $cacheEmpty = __DIR__ . self::TEST_ASSETS_PATH . 'cache/empty/';
        $cacheMono  = __DIR__ . self::TEST_ASSETS_PATH . 'cache/mono-server/';
        $cacheMulti = __DIR__ . self::TEST_ASSETS_PATH . 'cache/multi-servers/';
        return array(
            array($cacheEmpty, 'clear-cache', 'Cache cleared', $cacheEmpty),
            array($cacheMono,  'clear-cache', 'Cache cleared', $cacheEmpty),
            array($cacheMulti, 'clear-cache', 'Cache cleared', $cacheEmpty),
        );
    }

    /**
     * Test save method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Cache
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\CacheAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider saveTestDataProvider
     * @test
     */
    public function saveTest($cacheDir, $input, $expectedResult, $expectedSettingsFile)
    {
        // Create a temporary file for test purpose
        $fileName = 'projects.json';
        $tmpDir = $this->tmpCacheDir . basename($cacheDir) . '/';
        if (!file_exists($tmpDir . $this->bundleId)) {
            mkdir($tmpDir . $this->bundleId, 0755, true);
        }
        copy($cacheDir . $this->bundleId . DS . $fileName, $tmpDir . $this->bundleId . DS . $fileName);

        $redmine = new Redmine(new Settings($this->bundleId), new Workflow(), new Cache($this->bundleId, $tmpDir));
        $redmine->setDebug(true);
        $result  = $redmine->save('cache', $input);

        $this->assertJsonStringEqualsJsonString(
            file_get_contents($expectedSettingsFile . $this->bundleId . DS . $fileName),
            file_get_contents($tmpDir . $this->bundleId . DS . $fileName)
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for saveExceptionTest method
     *
     * @return array
     */
    public function saveExceptionTestDataProvider()
    {
        return array(
            array('clear', '\AlfredWorkflow\Redmine\Actions\Exception', 'Cache action clear does not exists.'),
        );
    }

    /**
     * Test save method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Cache
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\CacheAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider saveExceptionTestDataProvider
     * @test
     */
    public function saveExceptionTest($input, $expectedClass, $expectedMsg)
    {
        $this->setExpectedException(
            $expectedClass, $expectedMsg
        );
        $redmine = new Redmine(new Settings($this->bundleId), new Workflow(), new Cache($this->bundleId));
        $redmine->save('cache', $input);

    }

    public function tearDown()
    {
        exec('rm -rf ' . $this->tmpDir);
    }
}
