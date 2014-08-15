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
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configMulti              = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers/';
        $allActions               = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/all-actions.xml');
        $addAction                = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action.xml');
        $addActionTest            = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-test.xml');
        $addActionErrorIdentifier = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-error-identifier.xml');
        $addActionErrorUrl        = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-error-url.xml');
        $addActionParamsComplete  = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/add-action-params-complete.xml');
        $rmActionMonoServer       = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/rm-action-mono-server.xml');
        $rmActionMultiServers     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/configure/rm-action-multi-servers.xml');
        return array(
            array($configEmpty, '',                                                      $allActions),
            array($configEmpty, ' ',                                                     $allActions),
            array($configEmpty, 'add',                                                   $addAction),
            array($configEmpty, ' add',                                                  $addAction),
            array($configEmpty, 'add ',                                                  $addAction),
            array($configEmpty, ' add ',                                                 $addAction),
            array($configEmpty, ' add test',                                             $addActionTest),
            array($configMono,  ' add test1',                                            $addActionErrorIdentifier),
            array($configEmpty, ' add test ',                                            $addActionTest),
            array($configEmpty, ' add test http',                                        $addActionErrorUrl),
            array($configEmpty, ' add test http ',                                       $addActionErrorUrl),
            //array($configEmpty, ' add test http://redmine.test.com key Redmine server',  $addActionParamsComplete),
            //array($configEmpty, ' add test http://redmine.test.com key Redmine server ', $addActionParamsComplete),
            array($configMono,  'rm',                                                    $rmActionMonoServer),
            array($configMono,  ' rm',                                                   $rmActionMonoServer),
            array($configMono,  'rm ',                                                   $rmActionMonoServer),
            array($configMono,  ' rm ',                                                  $rmActionMonoServer),
            array($configMono,  ' rm test1',                                             $rmActionMonoServer),
            array($configMulti, 'rm',                                                    $rmActionMultiServers),
            array($configMulti, ' rm',                                                   $rmActionMultiServers),
            array($configMulti, 'rm ',                                                   $rmActionMultiServers),
            array($configMulti, ' rm ',                                                  $rmActionMultiServers),
            array($configMulti, ' rm test1',                                             $rmActionMonoServer),
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
     * @dataProvider runTestDataProvider
     * @test
     */
    public function testRun($config, $input, $expectedResultReturn)
    {
        $redmine = new Redmine(new Settings('test', $config), new Workflow());
        $result  = $redmine->run('config', $input);

        $this->assertEquals($expectedResultReturn, $result);
    }

    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function saveTestDataProvider()
    {
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty/';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server/';
        $configMulti              = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers/';
        $configTwoServers         = __DIR__ . self::TEST_ASSETS_PATH . 'config/2-servers/';
        return array(
            array($configEmpty,      'add test1 http://redmine.test1.com api-key-test1 Redmine server 1', 'Configuration added',   $configMono),
            array($configMono,       'add test2 http://redmine.test2.com api-key-test2 Redmine server 2', 'Configuration added',   $configTwoServers),
            array($configMono,       'rm test1',                                                          'Configuration removed', $configEmpty),
            array($configTwoServers, 'rm test2',                                                          'Configuration removed', $configMono),
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
     * @dataProvider saveTestDataProvider
     * @test
     */
    public function saveTest($configDir, $input, $expectedResult, $expectedSettingsFile)
    {
        // Create a temporary file for test purpose
        $fileName = 'settings.json';
        $tmpDir = $this->tmpDir . basename($configDir) . '/';
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        copy($configDir . $fileName, $tmpDir . $fileName);

        $redmine = new Redmine(new Settings('test', $tmpDir), new Workflow());
        $result  = $redmine->save('config', $input);

        $this->assertJsonStringEqualsJsonString(file_get_contents($expectedSettingsFile . $fileName), file_get_contents($tmpDir . $fileName));
        // Remove the temporary settings file
        unlink($tmpDir . $fileName);

        $this->assertEquals($expectedResult, $result);
    }
}
