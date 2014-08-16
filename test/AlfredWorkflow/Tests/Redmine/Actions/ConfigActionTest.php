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
 * Test class for AlfredWorkflow\Redmine\Actions\ConfigAction class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class ConfigActionTest extends \PHPUnit_Framework_TestCase
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
    public function testRunDataProvider()
    {
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configMulti              = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers/';
        $allActions               = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/all-actions.xml');
        $rmActionMonoServer       = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/rm-action-mono-server.xml');
        $rmActionMultiServers     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/rm-action-multi-servers.xml');
        return array(
            array($configEmpty, '',              $allActions),
            array($configEmpty, ' ',             $allActions),
            array($configMono,  'remove',        $rmActionMonoServer),
            array($configMono,  ' remove',       $rmActionMonoServer),
            array($configMono,  'remove ',       $rmActionMonoServer),
            array($configMono,  ' remove ',      $rmActionMonoServer),
            array($configMono,  ' remove test1', $rmActionMonoServer),
            array($configMulti, 'remove',        $rmActionMultiServers),
            array($configMulti, ' remove',       $rmActionMultiServers),
            array($configMulti, 'remove ',       $rmActionMultiServers),
            array($configMulti, ' remove ',      $rmActionMultiServers),
            array($configMulti, ' remove test1', $rmActionMonoServer),
        );
    }

    /**
     * Test run method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Settings
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\ConfigAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider testRunDataProvider
     * @test
     */
    public function testRun($config, $input, $expectedResultReturn)
    {
        $redmine = new Redmine(
            new Settings($this->bundleId, $config),
            new Workflow(),
            new Cache($this->bundleId, $this->tmpCacheDir)
        );
        $result  = $redmine->run('config', $input);

        $this->assertEquals($expectedResultReturn, $result);
    }

    /**
     * Data provider for testRunAdd method
     *
     * @return array
     */
    public function testRunAddDataProvider()
    {
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configMulti              = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers/';
        $addAction                = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action.xml');
        $addActionTest            = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-test.xml');
        $addActionErrorIdentifier = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-error-identifier.xml');
        $addActionErrorUrl        = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-error-url.xml');
        $addActionErrorKey        = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-error-key.xml');
        $addActionParamsComplete  = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-params-complete.xml');
        return array(
            array($configEmpty, 'add',                                                       $addAction),
            array($configEmpty, ' add',                                                      $addAction),
            array($configEmpty, 'add ',                                                      $addAction),
            array($configEmpty, ' add ',                                                     $addAction),
            array($configEmpty, 'add test',                                                  $addActionTest),
            array($configMono,  'add test1',                                                 $addActionErrorIdentifier),
            array($configEmpty, 'add test ',                                                 $addActionTest),
            array($configEmpty, 'add test http',                                             $addActionErrorUrl),
            array($configEmpty, 'add test http ',                                            $addActionErrorUrl),
            array($configEmpty, 'add test http://redmine.test.com wrong-key Redmine Server', $addActionErrorKey),
            array($configEmpty, 'add test http://redmine.test.com key Redmine server',       $addActionParamsComplete),
            array($configEmpty, 'add test http://redmine.test.com key Redmine server ',      $addActionParamsComplete),
        );
    }

    /**
     * Test run method for add action on AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Settings
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\ConfigAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider testRunAddDataProvider
     * @test
     */
    public function testRunAdd($config, $input, $expectedResultReturn)
    {
        $params  = explode(' ', $input);
        $clients = array();
        if (count($params) >= 4) {
            $client = $this->getMockBuilder('Redmine\Client')
                ->disableOriginalConstructor()
                ->setMethods(array('api'))
                ->getMock();
            $user = $this->getMockBuilder('Redmine\Api\User')
                ->disableOriginalConstructor()
                ->setMethods(array('getCurrentUser'))
                ->getMock();
            $client->expects($this->any())
                ->method('api')
                ->will(
                    $this->returnValueMap(
                        array(
                            array('user', $user),
                        )
                    )
                );
            $userReturn = ($params[3] == 'wrong-key') ? false : true;
            $user->expects($this->any())
                ->method('getCurrentUser')
                ->willReturn($userReturn);
            $clients = array('test' => $client);
        }
        $redmine = new Redmine(
            new Settings($this->bundleId, $config),
            new Workflow(),
            new Cache($this->bundleId,$this->tmpCacheDir),
            $clients
        );
        $result  = $redmine->run('config', $input);

        $this->assertEquals($expectedResultReturn, $result);
    }

    /**
     * Data provider for testSave method
     *
     * @return array
     */
    public function testSaveDataProvider()
    {
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configTwoServers         = __DIR__ . self::TEST_ASSETS_PATH . 'config/2-servers/';
        return array(
            array($configEmpty,      'add test1 http://redmine.test1.com api-key-test1 Redmine server 1', 'Configuration added',   $configMono),
            array($configMono,       'add test2 http://redmine.test2.com api-key-test2 Redmine server 2', 'Configuration added',   $configTwoServers),
            array($configMono,       'remove test1',                                                      'Configuration removed', $configEmpty),
            array($configTwoServers, 'remove test2',                                                      'Configuration removed', $configMono),
        );
    }
    /**
     * Test save method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Settings
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\ConfigAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider testSaveDataProvider
     * @test
     */
    public function testSave($configDir, $input, $expectedResult, $expectedSettingsFile)
    {
        // Create a temporary file for test purpose
        $fileName = 'settings.json';
        $tmpDir = $this->tmpDir . basename($configDir) . DS;
        if (!file_exists($tmpDir . $this->bundleId . DS)) {
            mkdir($tmpDir . $this->bundleId . DS, 0755, true);
        }
        copy($configDir . $this->bundleId . DS . $fileName, $tmpDir . $this->bundleId . DS . $fileName);

        $redmine = new Redmine(
            new Settings($this->bundleId, $tmpDir),
            new Workflow(),
            new Cache($this->bundleId, $this->tmpCacheDir)
        );
        $result  = $redmine->save('config', $input);

        $this->assertJsonStringEqualsJsonString(
            file_get_contents($expectedSettingsFile . $this->bundleId . DS . $fileName),
            file_get_contents($tmpDir . $this->bundleId . DS . $fileName)
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function tearDown()
    {
        exec('rm -rf ' . $this->tmpDir);
    }
}
