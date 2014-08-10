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

namespace AlfredWorkflow\Tests\Redmine\actions;

use AlfredWorkflow\Redmine;
use AlfredWorkflow\Redmine\Storage\Settings;
use AlfredWorkflow\Redmine\Storage\Cache;
use Alfred\Workflow;

/**
 * Test class for AlfredWorkflow\Redmine
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class PageActionTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpCacheDir   = '';
    protected $bundleId      = 'test';
    const TEST_ASSETS_PATH   = '/../../../../data/';

    public function setUp()
    {
        $this->tmpCacheDir   = __DIR__ . '/../../../../tmp/cache/';
        if (!file_exists($this->tmpCacheDir . $this->bundleId)) {
            mkdir($this->tmpCacheDir . $this->bundleId, 0755, true);
        }
    }

    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function getProjectsDataProvider()
    {
        $configEmpty              = __DIR__ . self::TEST_ASSETS_PATH . 'config/empty';
        $configMono               = __DIR__ . self::TEST_ASSETS_PATH . 'config/mono-server';
        $configMulti              = __DIR__ . self::TEST_ASSETS_PATH . 'config/multi-servers';
        $apiProjectsTest1         = json_decode(file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'api/projects-test1.json'), true);
        $apiProjectsMonoServer    = array($apiProjectsTest1);
        $apiProjectsTest2         = json_decode(file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'api/projects-test2.json'), true);
        $apiProjectsMultiServers  = array($apiProjectsTest1, $apiProjectsTest2);
        $apiMyProject1Test1Pages  = json_decode(file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'api/wikipages-test1-myproject1.json'), true);
        $apiMyProject2Test1Pages  = json_decode(file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'api/wikipages-test1-myproject2.json'), true);
        $configActions            = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/config-actions.xml');
        $allActions               = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-actions.xml');
        $allActionsTest1          = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-actions-test1.xml');
        $allProjectsTest1WikiMono = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test1-wiki-mono.xml');
        $allProjectsTest1Wiki     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test1-wiki.xml');
        $allProjectsTest1Home     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test1-home.xml');
        $allProjectsTest1Issues   = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test1-issues.xml');
        $myProjectsTest1Wiki      = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/my-projects-test1-wiki.xml');
        $myProject1Test1WikiPages = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-wikipages-myproject1.xml');
        $myProject2Test1WikiPages = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-wikipages-myproject2.xml');
        $myProjectsTest1WikiMono  = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/my-projects-test1-wiki-mono.xml');
        $myProjectsTest1Home      = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/my-projects-test1-home.xml');
        $myProjectsTest1Issues    = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/my-projects-test1-issues.xml');
        $theProjectsTest1Wiki     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/the-projects-test1-wiki.xml');
        $theProjectsTest1WikiMono = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/the-projects-test1-wiki-mono.xml');
        $theProjectsTest1Home     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/the-projects-test1-home.xml');
        $theProjectsTest1Issues   = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/the-projects-test1-issues.xml');
        $theProject2Test1WikiPage = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/no-wikipage-theproject2.xml');
        $issueTest1               = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/issue-test1.xml');
        $issue123Test1            = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/issue-123-test1.xml');
        $allActionsTest2          = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-actions-test2.xml');
        $allProjectsTest2Wiki     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test2-wiki.xml');
        $allProjectsTest2Home     = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test2-home.xml');
        $allProjectsTest2Issues   = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-projects-test2-issues.xml');
        $issueTest2               = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/issue-test2.xml');
        $issue123Test2            = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/issue-123-test2.xml');
        $allRedmines              = file_get_contents(__DIR__ . self::TEST_ASSETS_PATH . 'results/all-redmines.xml');

        return array(
            array($configEmpty, '',                              array(),                                                                   $configActions),
            array($configMono,  '',                              array('project' => $apiProjectsMonoServer),                                     $allActions),
            array($configMono,  ' ',                             array('project' => $apiProjectsMonoServer),                                     $allActions),
            array($configMono,  'home',                          array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1Home),
            array($configMono,  'home ',                         array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1Home),
            array($configMono,  'home my',                       array('project' => $apiProjectsMonoServer),                                     $myProjectsTest1Home),
            array($configMono,  'home the',                      array('project' => $apiProjectsMonoServer),                                     $theProjectsTest1Home),
            array($configMono,  'wiki',                          array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1WikiMono),
            array($configMono,  'wiki ',                         array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1WikiMono),
            array($configMono,  'wiki my',                       array('project' => $apiProjectsMonoServer),                                     $myProjectsTest1WikiMono),
            array($configMono,  'wiki myproject-1-test1',        array('project' => $apiProjectsMonoServer, 'wiki' => $apiMyProject1Test1Pages), $myProject1Test1WikiPages),
            array($configMono,  'wiki myproject-2-test1',        array('project' => $apiProjectsMonoServer, 'wiki' => $apiMyProject2Test1Pages), $myProject2Test1WikiPages),
            array($configMono,  'wiki theproject-2-test1',       array('project' => $apiProjectsMonoServer, 'wiki' => ''),                       $theProject2Test1WikiPage),
            array($configMono,  'wiki the',                      array('project' => $apiProjectsMonoServer),                                     $theProjectsTest1WikiMono),
            array($configMono,  'issues',                        array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1Issues),
            array($configMono,  'issues ',                       array('project' => $apiProjectsMonoServer),                                     $allProjectsTest1Issues),
            array($configMono,  'issues my',                     array('project' => $apiProjectsMonoServer),                                     $myProjectsTest1Issues),
            array($configMono,  'issues the',                    array('project' => $apiProjectsMonoServer),                                     $theProjectsTest1Issues),
            array($configMono,  'issue ',                        array('project' => $apiProjectsMonoServer),                                     $issueTest1),
            array($configMono,  'issue 123',                     array('project' => $apiProjectsMonoServer),                                     $issue123Test1),
            array($configMulti, '',                              array('project' => $apiProjectsMultiServers),                                     $allRedmines),
            array($configMulti, ' ',                             array('project' => $apiProjectsMultiServers),                                     $allRedmines),
            array($configMulti, 'test1',                         array('project' => $apiProjectsMultiServers),                                     $allActionsTest1),
            array($configMulti, ' test1',                        array('project' => $apiProjectsMultiServers),                                     $allActionsTest1),
            array($configMulti, ' test1 ',                       array('project' => $apiProjectsMultiServers),                                     $allActionsTest1),
            array($configMulti, 'test1 ',                        array('project' => $apiProjectsMultiServers),                                     $allActionsTest1),
            array($configMulti, 'test1 home',                    array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Home),
            array($configMulti, 'test1 home ',                   array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Home),
            array($configMulti, 'test1 home my',                 array('project' => $apiProjectsMultiServers),                                     $myProjectsTest1Home),
            array($configMulti, 'test1 home the',                array('project' => $apiProjectsMultiServers),                                     $theProjectsTest1Home),
            array($configMulti, 'test1 wiki',                    array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki ',                   array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki my',                 array('project' => $apiProjectsMultiServers),                                     $myProjectsTest1Wiki),
            array($configMulti, 'test1 wiki myproject-1-test1',  array('project' => $apiProjectsMultiServers, 'wiki' => $apiMyProject1Test1Pages), $myProject1Test1WikiPages),
            array($configMulti, 'test1 wiki myproject-2-test1',  array('project' => $apiProjectsMultiServers, 'wiki' => $apiMyProject2Test1Pages), $myProject2Test1WikiPages),
            array($configMulti, 'test1 wiki the',                array('project' => $apiProjectsMultiServers),                                     $theProjectsTest1Wiki),
            array($configMulti, 'test1 wiki theproject-2-test1', array('project' => $apiProjectsMultiServers, 'wiki' => ''),                       $theProject2Test1WikiPage),
            array($configMulti, 'test1 issues',                  array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Issues),
            array($configMulti, 'test1 issues ',                 array('project' => $apiProjectsMultiServers),                                     $allProjectsTest1Issues),
            array($configMulti, 'test1 issues my',               array('project' => $apiProjectsMultiServers),                                     $myProjectsTest1Issues),
            array($configMulti, 'test1 issues the',              array('project' => $apiProjectsMultiServers),                                     $theProjectsTest1Issues),
            array($configMulti, 'test1 issue ',                  array('project' => $apiProjectsMultiServers),                                     $issueTest1),
            array($configMulti, 'test1 issue 123',               array('project' => $apiProjectsMultiServers),                                     $issue123Test1),
            array($configMulti, 'test2',                         array('project' => $apiProjectsMultiServers),                                     $allActionsTest2),
            array($configMulti, ' test2',                        array('project' => $apiProjectsMultiServers),                                     $allActionsTest2),
            array($configMulti, ' test2 ',                       array('project' => $apiProjectsMultiServers),                                     $allActionsTest2),
            array($configMulti, 'test2 ',                        array('project' => $apiProjectsMultiServers),                                     $allActionsTest2),
            array($configMulti, 'test2 home',                    array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Home),
            array($configMulti, 'test2 home ',                   array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Home),
            array($configMulti, 'test2 wiki',                    array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Wiki),
            array($configMulti, 'test2 wiki ',                   array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Wiki),
            array($configMulti, 'test2 issues',                  array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Issues),
            array($configMulti, 'test2 issues ',                 array('project' => $apiProjectsMultiServers),                                     $allProjectsTest2Issues),
            array($configMulti, 'test2 issue ',                  array('project' => $apiProjectsMultiServers),                                     $issueTest2),
            array($configMulti, 'test2 issue 123',               array('project' => $apiProjectsMultiServers),                                     $issue123Test2),
        );
    }

    /**
     * Test method for AlfredWorkflow\Redmine class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Storage\Settings
     * @covers AlfredWorkflow\Redmine\Storage\Cache
     * @covers AlfredWorkflow\Redmine\Storage\Json
     * @covers AlfredWorkflow\Redmine\Actions\PageAction
     * @covers AlfredWorkflow\Redmine\Actions\BaseAction
     * @dataProvider getProjectsDataProvider
     * @test
     */
    public function testRun($config, $input, $apiReturn, $expectedResult)
    {
        $configArray = json_decode(file_get_contents($config . '/settings.json'), true);
        $clients     = array();

        if (is_array($configArray) && count($configArray)) {
            // Create the used mock objects
            $client = $this->getMockBuilder('Redmine\Client')
                ->disableOriginalConstructor()
                ->setMethods(array('api', 'all'))
                ->getMock();
            $project = $this->getMockBuilder('Redmine\Api\Project')
                ->disableOriginalConstructor()
                ->setMethods(array('all'))
                ->getMock();
            $wiki = $this->getMockBuilder('Redmine\Api\Wiki')
                ->disableOriginalConstructor()
                ->setMethods(array('all'))
                ->getMock();
            $client->expects($this->any())
                ->method('api')
                ->will(
                    $this->returnValueMap(
                        array(
                            array('project', $project),
                            array('wiki',    $wiki)
                        )
                    )
                );
            if (count($apiReturn['project']) == 2) {
                $project->expects($this->exactly(count($apiReturn['project'])))
                    ->method('all')
                    ->will($this->onConsecutiveCalls($apiReturn['project'][0], $apiReturn['project'][1]));
                //->will(
                //    $this->returnValueMap(
                //        $apiReturn['project']
                //    )
                //);

            } else {
                $project->expects($this->exactly(count($apiReturn['project'])))
                    ->method('all')
                    ->will($this->returnValue($apiReturn['project'][0]));
                //->will(
                //    $this->returnValueMap(
                //        $apiReturn['project']
                //    )
                //);
            }

            if (count($apiReturn) == 2) {
                $wiki->expects($this->any())
                    ->method('all')
                    ->willReturn($apiReturn['wiki']);
            }

            foreach (array_keys($configArray) as $redmineId) {
                $clients[$redmineId] = $client;
            }
        }

        $redmine = new Redmine(new Settings('test', $config), new Workflow(), new Cache('test', $this->tmpCacheDir), $clients);
        $result  = $redmine->run('page', $input);

        $this->assertEquals($expectedResult, $result);
    }

    public function tearDown()
    {
        exec('rm -rf ' . $this->tmpCacheDir);
    }
}
