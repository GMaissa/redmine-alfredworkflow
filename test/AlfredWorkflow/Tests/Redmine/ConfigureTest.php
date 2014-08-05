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

namespace AlfredWorkflow\Tests\Redmine;

use Alfred\Workflow;
use AlfredWorkflow\Redmine\Configure;
use AlfredWorkflow\Redmine\Settings;

/**
 * Test class for AlfredWorkflow\Redmine\Configure
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir = '';

    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/../../../tmp/';
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
        $configEmpty              = __DIR__ . '/../../../data/config/empty.json';
        $configMono               = __DIR__ . '/../../../data/config/mono-server.json';
        $configMulti              = __DIR__ . '/../../../data/config/multi-servers.json';
        $allActions               = file_get_contents(__DIR__ . '/../../../data/results/configure/all-actions.xml');
        $addAction                = file_get_contents(__DIR__ . '/../../../data/results/configure/add-action.xml');
        $addActionTest            = file_get_contents(__DIR__ . '/../../../data/results/configure/add-action-test.xml');
        $addActionErrorIdentifier = file_get_contents(__DIR__ . '/../../../data/results/configure/add-action-error-identifier.xml');
        $addActionErrorUrl        = file_get_contents(__DIR__ . '/../../../data/results/configure/add-action-error-url.xml');
        $addActionParamsComplete  = file_get_contents(__DIR__ . '/../../../data/results/configure/add-action-params-complete.xml');
        $rmActionMonoServer       = file_get_contents(__DIR__ . '/../../../data/results/configure/rm-action-mono-server.xml');
        $rmActionMultiServers     = file_get_contents(__DIR__ . '/../../../data/results/configure/rm-action-multi-servers.xml');
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
            array($configEmpty, ' add test http://redmine.test.com key Redmine server',  $addActionParamsComplete),
            array($configEmpty, ' add test http://redmine.test.com key Redmine server ', $addActionParamsComplete),
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
     * @covers       AlfredWorkflow\Redmine\Configure
     * @covers       AlfredWorkflow\Redmine\Settings
     * @dataProvider runTestDataProvider
     * @test
     */
    public function testRun($configFile, $input, $expectedResultReturn)
    {
        $configureWorkflow = new Configure(new Settings($configFile), new Workflow());
        $result            = $configureWorkflow->run($input);

        $this->assertEquals($expectedResultReturn, $result);
    }

    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function saveTestDataProvider()
    {
        $configEmpty              = __DIR__ . '/../../../data/config/empty.json';
        $configMono               = __DIR__ . '/../../../data/config/mono-server.json';
        $configMulti              = __DIR__ . '/../../../data/config/multi-servers.json';
        return array(
            array($configEmpty, 'add test1 http://redmine.test1.com api-key-test1 Redmine server 1', 'Configuration added',   $configMono),
            array($configMono,  'add test2 http://redmine.test2.com api-key-test2 Redmine server 2', 'Configuration added',   $configMulti),
            array($configMono,  'rm test1',                                                          'Configuration removed', $configEmpty),
            array($configMulti, 'rm test2',                                                          'Configuration removed', $configMono),
        );
    }
    /**
     * Test save method for AlfredWorkflow\Redmine\Configure class
     *
     * @covers       AlfredWorkflow\Redmine\Configure
     * @covers       AlfredWorkflow\Redmine\Settings
     * @dataProvider saveTestDataProvider
     * @test
     */
    public function saveTest($configFile, $input, $expectedResult, $expectedSettingsFile)
    {
        // Create a temporary file for test purpose
        $tmpFile = $this->tmpDir . basename($configFile);
        copy($configFile, $tmpFile);

        $configureWorkflow = new Configure(new Settings($tmpFile), new Workflow());
        $result            = $configureWorkflow->save($input);

        $this->assertJsonStringEqualsJsonString(file_get_contents($expectedSettingsFile), file_get_contents($tmpFile));
        // Remove the temporary settings file
        unlink($tmpFile);

        $this->assertEquals($expectedResult, $result);
    }
}
