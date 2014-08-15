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
    protected $bundleId      = 'test';
    const TEST_ASSETS_PATH   = '/../../../../data/';

    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/../../../../tmp/';
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
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
        $allActions  = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/cache/all-actions.xml');
        return array(
            array($configEmpty, '',            $allActions),
            array($configMono,  '',            $allActions),
            array($configMulti, '',            $allActions),
            array($configEmpty, ' ',           $allActions),
            array($configMono,  ' ',           $allActions),
            array($configMulti, ' ',           $allActions),
            array($configEmpty, 'clear',       $allActions),
            array($configMono,  'clear',       $allActions),
            array($configMulti, 'clear',       $allActions),
            array($configEmpty, 'clear-cache', $allActions),
            array($configMono,  'clear-cache', $allActions),
            array($configMulti, 'clear-cache', $allActions),
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
    public function testRun($config, $input, $expectedResultReturn)
    {
        $redmine = new Redmine(new Settings('test', $config), new Workflow(), new Cache('test'));
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
        $fileName = 'cache-projects.json';
        $tmpDir = $this->tmpDir . basename($cacheDir) . '/';
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        copy($cacheDir . $fileName, $tmpDir . $fileName);

        $redmine = new Redmine(new Settings('test'), new Workflow(), new Cache('test', $tmpDir));
        $result  = $redmine->save('cache', $input);

        $this->assertJsonStringEqualsJsonString(file_get_contents($expectedSettingsFile . $fileName), file_get_contents($tmpDir . $fileName));
        // Remove the temporary cache file
        unlink($tmpDir . $fileName);

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
        $redmine = new Redmine(new Settings('test'), new Workflow(), new Cache('test'));
        $redmine->save('cache', $input);

    }
}
