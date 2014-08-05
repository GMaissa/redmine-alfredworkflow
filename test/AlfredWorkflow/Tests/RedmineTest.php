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

use AlfredWorkflow\Redmine,
    AlfredWorkflow\Redmine\Settings,
    Alfred\Workflow;

/**
 * Test class for AlfredWorkflow\Redmine
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class RedmineProjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testRun method
     *
     * @return array
     */
    public function getProjectsDataProvider()
    {
        $configEmpty              = __DIR__ . '/../../data/config/empty.json';
        $configMono               = __DIR__ . '/../../data/config/mono-server.json';
        $configMulti              = __DIR__ . '/../../data/config/multi-servers.json';
        $apiProjectsTest1         = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test1.json'), true);
        $apiProjectsTest2         = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test2.json'), true);
        $apiMyProject1Test1Pages  = json_decode(file_get_contents(__DIR__ . '/../../data/api/wikipages-test1-myproject1.json'), true);
        $apiMyProject2Test1Pages  = json_decode(file_get_contents(__DIR__ . '/../../data/api/wikipages-test1-myproject2.json'), true);
        $configActions            = file_get_contents(__DIR__ . '/../../data/results/config-actions.xml');
        $allActions               = file_get_contents(__DIR__ . '/../../data/results/all-actions.xml');
        $allActionsTest1          = file_get_contents(__DIR__ . '/../../data/results/all-actions-test1.xml');
        $allProjectsTest1WikiMono = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-wiki-mono.xml');
        $allProjectsTest1Wiki     = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-wiki.xml');
        $allProjectsTest1Home     = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-home.xml');
        $allProjectsTest1Issues   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-issues.xml');
        $myProjectsTest1Wiki      = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-wiki.xml');
        $myProject1Test1WikiPages = file_get_contents(__DIR__ . '/../../data/results/all-wikipages-myproject1.xml');
        $myProject2Test1WikiPages = file_get_contents(__DIR__ . '/../../data/results/all-wikipages-myproject2.xml');
        $myProjectsTest1WikiMono  = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-wiki-mono.xml');
        $myProjectsTest1Home      = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-home.xml');
        $myProjectsTest1Issues    = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-issues.xml');
        $theProjectsTest1Wiki     = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-wiki.xml');
        $theProjectsTest1WikiMono = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-wiki-mono.xml');
        $theProjectsTest1Home     = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-home.xml');
        $theProjectsTest1Issues   = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-issues.xml');
        $theProject2Test1WikiPage = file_get_contents(__DIR__ . '/../../data/results/no-wikipage-theproject2.xml');
        $issueTest1               = file_get_contents(__DIR__ . '/../../data/results/issue-test1.xml');
        $issue123Test1            = file_get_contents(__DIR__ . '/../../data/results/issue-123-test1.xml');
        $allActionsTest2          = file_get_contents(__DIR__ . '/../../data/results/all-actions-test2.xml');
        $allProjectsTest2Wiki     = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-wiki.xml');
        $allProjectsTest2Home     = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-home.xml');
        $allProjectsTest2Issues   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-issues.xml');
        $issueTest2               = file_get_contents(__DIR__ . '/../../data/results/issue-test2.xml');
        $issue123Test2            = file_get_contents(__DIR__ . '/../../data/results/issue-123-test2.xml');
        $allRedmines              = file_get_contents(__DIR__ . '/../../data/results/all-redmines.xml');

        return array(
            array($configEmpty, '',                              array(),                                                                   $configActions),
            array($configMono,  '',                              array(),                                                                   $allActions),
            array($configMono,  ' ',                             array(),                                                                   $allActions),
            array($configMono,  'home',                          array('project' => $apiProjectsTest1),                                     $allProjectsTest1Home),
            array($configMono,  'home ',                         array('project' => $apiProjectsTest1),                                     $allProjectsTest1Home),
            array($configMono,  'home my',                       array('project' => $apiProjectsTest1),                                     $myProjectsTest1Home),
            array($configMono,  'home the',                      array('project' => $apiProjectsTest1),                                     $theProjectsTest1Home),
            array($configMono,  'wiki',                          array('project' => $apiProjectsTest1),                                     $allProjectsTest1WikiMono),
            array($configMono,  'wiki ',                         array('project' => $apiProjectsTest1),                                     $allProjectsTest1WikiMono),
            array($configMono,  'wiki my',                       array('project' => $apiProjectsTest1),                                     $myProjectsTest1WikiMono),
            array($configMono,  'wiki myproject-1-test1',        array('project' => $apiProjectsTest1, 'wiki' => $apiMyProject1Test1Pages), $myProject1Test1WikiPages),
            array($configMono,  'wiki myproject-2-test1',        array('project' => $apiProjectsTest1, 'wiki' => $apiMyProject2Test1Pages), $myProject2Test1WikiPages),
            array($configMono,  'wiki theproject-2-test1',       array('project' => $apiProjectsTest1, 'wiki' => ''),                       $theProject2Test1WikiPage),
            array($configMono,  'wiki the',                      array('project' => $apiProjectsTest1),                                     $theProjectsTest1WikiMono),
            array($configMono,  'issues',                        array('project' => $apiProjectsTest1),                                     $allProjectsTest1Issues),
            array($configMono,  'issues ',                       array('project' => $apiProjectsTest1),                                     $allProjectsTest1Issues),
            array($configMono,  'issues my',                     array('project' => $apiProjectsTest1),                                     $myProjectsTest1Issues),
            array($configMono,  'issues the',                    array('project' => $apiProjectsTest1),                                     $theProjectsTest1Issues),
            array($configMono,  'issue ',                        array(),                                                                   $issueTest1),
            array($configMono,  'issue 123',                     array(),                                                                   $issue123Test1),
            array($configMulti, '',                              array(),                                                                   $allRedmines),
            array($configMulti, ' ',                             array(),                                                                   $allRedmines),
            array($configMulti, 'test1',                         array(),                                                                   $allActionsTest1),
            array($configMulti, ' test1',                        array(),                                                                   $allActionsTest1),
            array($configMulti, ' test1 ',                       array(),                                                                   $allActionsTest1),
            array($configMulti, 'test1 ',                        array(),                                                                   $allActionsTest1),
            array($configMulti, 'test1 home',                    array('project' => $apiProjectsTest1),                                     $allProjectsTest1Home),
            array($configMulti, 'test1 home ',                   array('project' => $apiProjectsTest1),                                     $allProjectsTest1Home),
            array($configMulti, 'test1 home my',                 array('project' => $apiProjectsTest1),                                     $myProjectsTest1Home),
            array($configMulti, 'test1 home the',                array('project' => $apiProjectsTest1),                                     $theProjectsTest1Home),
            array($configMulti, 'test1 wiki',                    array('project' => $apiProjectsTest1),                                     $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki ',                   array('project' => $apiProjectsTest1),                                     $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki my',                 array('project' => $apiProjectsTest1),                                     $myProjectsTest1Wiki),
            array($configMulti, 'test1 wiki myproject-1-test1',  array('project' => $apiProjectsTest1, 'wiki' => $apiMyProject1Test1Pages), $myProject1Test1WikiPages),
            array($configMulti, 'test1 wiki myproject-2-test1',  array('project' => $apiProjectsTest1, 'wiki' => $apiMyProject2Test1Pages), $myProject2Test1WikiPages),
            array($configMulti, 'test1 wiki the',                array('project' => $apiProjectsTest1),                                     $theProjectsTest1Wiki),
            array($configMulti, 'test1 wiki theproject-2-test1', array('project' => $apiProjectsTest1, 'wiki' => ''),                       $theProject2Test1WikiPage),
            array($configMulti, 'test1 issues',                  array('project' => $apiProjectsTest1),                                     $allProjectsTest1Issues),
            array($configMulti, 'test1 issues ',                 array('project' => $apiProjectsTest1),                                     $allProjectsTest1Issues),
            array($configMulti, 'test1 issues my',               array('project' => $apiProjectsTest1),                                     $myProjectsTest1Issues),
            array($configMulti, 'test1 issues the',              array('project' => $apiProjectsTest1),                                     $theProjectsTest1Issues),
            array($configMulti, 'test1 issue ',                  array(),                                                                   $issueTest1),
            array($configMulti, 'test1 issue 123',               array(),                                                                   $issue123Test1),
            array($configMulti, 'test2',                         array(),                                                                   $allActionsTest2),
            array($configMulti, ' test2',                        array(),                                                                   $allActionsTest2),
            array($configMulti, ' test2 ',                       array(),                                                                   $allActionsTest2),
            array($configMulti, 'test2 ',                        array(),                                                                   $allActionsTest2),
            array($configMulti, 'test2 home',                    array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Home),
            array($configMulti, 'test2 home ',                   array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Home),
            array($configMulti, 'test2 wiki',                    array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Wiki),
            array($configMulti, 'test2 wiki ',                   array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Wiki),
            array($configMulti, 'test2 issues',                  array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Issues),
            array($configMulti, 'test2 issues ',                 array('project'=>$apiProjectsTest2),                                       $allProjectsTest2Issues),
            array($configMulti, 'test2 issue ',                  array(),                                                                   $issueTest2),
            array($configMulti, 'test2 issue 123',               array(),                                                                   $issue123Test2),
        );
    }

    /**
     * Test method for AlfredWorkflow\Redmine class
     *
     * @covers AlfredWorkflow\Redmine
     * @covers AlfredWorkflow\Redmine\Settings
     * @dataProvider getProjectsDataProvider
     * @test
     */
    public function testRun($config, $input, $apiReturn, $expectedResult)
    {
        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
                       ->disableOriginalConstructor()
                       ->setMethods(array('api', 'all'))
                       ->getMock();

        if (count($apiReturn) == 2) {
            $client->expects($this->exactly(count($apiReturn)))
                ->method('api')
                ->with(
                    $this->logicalOr(
                       $this->equalTo('project'),
                       $this->equalTo('wiki')
                    )
                )
                ->willReturn($client);
            $client->expects($this->exactly(count($apiReturn)))
                ->method('all')
                ->will($this->onConsecutiveCalls($apiReturn['project'], $apiReturn['wiki']));
        } elseif (count($apiReturn) == 1) {
            $client->expects($this->once())
                ->method('api')
                ->with('project')
                ->willReturn($client);
            $client->expects($this->once())
                ->method('all')
                ->willReturn($apiReturn['project']);
        }

        $redmine = new Redmine(new Settings($config), new Workflow(), $client);
        $result  = $redmine->run($input);

        $this->assertEquals($expectedResult, $result);
    }
}
