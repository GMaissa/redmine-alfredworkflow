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

use AlfredWorkflow\RedmineProject,
    Alfred\Workflow;

/**
 * Test class for AlfredWorkflow\RedmineProject
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
        $configMono             = file_get_contents(__DIR__ . '/../../data/config/mono-server.json');
        $configMulti            = file_get_contents(__DIR__ . '/../../data/config/multi-servers.json');
        $apiProjectsTest1       = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test1.json'),   TRUE);
        $apiProjectsTest2       = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test2.json'),   TRUE);
        $allActions             = file_get_contents(__DIR__ . '/../../data/results/all-actions.xml');
        $allActionsTest1        = file_get_contents(__DIR__ . '/../../data/results/all-actions-test1.xml');
        $allProjectsTest1Wiki   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-wiki.xml');
        $allProjectsTest1Home   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-home.xml');
        $allProjectsTest1Issues = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1-issues.xml');
        $myProjectsTest1Wiki    = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-wiki.xml');
        $myProjectsTest1Home    = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-home.xml');
        $myProjectsTest1Issues  = file_get_contents(__DIR__ . '/../../data/results/my-projects-test1-issues.xml');
        $theProjectsTest1Wiki   = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-wiki.xml');
        $theProjectsTest1Home   = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-home.xml');
        $theProjectsTest1Issues = file_get_contents(__DIR__ . '/../../data/results/the-projects-test1-issues.xml');
        $issueTest1             = file_get_contents(__DIR__ . '/../../data/results/issue-test1.xml');
        $issue123Test1          = file_get_contents(__DIR__ . '/../../data/results/issue-123-test1.xml');
        $allActionsTest2        = file_get_contents(__DIR__ . '/../../data/results/all-actions-test2.xml');
        $allProjectsTest2Wiki   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-wiki.xml');
        $allProjectsTest2Home   = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-home.xml');
        $allProjectsTest2Issues = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2-issues.xml');
        $issueTest2             = file_get_contents(__DIR__ . '/../../data/results/issue-test2.xml');
        $issue123Test2          = file_get_contents(__DIR__ . '/../../data/results/issue-123-test2.xml');
        $allRedmines            = file_get_contents(__DIR__ . '/../../data/results/all-redmines.xml');

        return array(
            array($configMono,  '',                 '',                $allActions),
            array($configMono,  ' ',                '',                $allActions),
            array($configMono,  'home',             $apiProjectsTest1, $allProjectsTest1Home),
            array($configMono,  'home ',            $apiProjectsTest1, $allProjectsTest1Home),
            array($configMono,  'home my',          $apiProjectsTest1, $myProjectsTest1Home),
            array($configMono,  'home the',         $apiProjectsTest1, $theProjectsTest1Home),
            array($configMono,  'wiki',             $apiProjectsTest1, $allProjectsTest1Wiki),
            array($configMono,  'wiki ',            $apiProjectsTest1, $allProjectsTest1Wiki),
            array($configMono,  'wiki my',          $apiProjectsTest1, $myProjectsTest1Wiki),
            array($configMono,  'wiki the',         $apiProjectsTest1, $theProjectsTest1Wiki),
            array($configMono,  'issues',           $apiProjectsTest1, $allProjectsTest1Issues),
            array($configMono,  'issues ',          $apiProjectsTest1, $allProjectsTest1Issues),
            array($configMono,  'issues my',        $apiProjectsTest1, $myProjectsTest1Issues),
            array($configMono,  'issues the',       $apiProjectsTest1, $theProjectsTest1Issues),
            array($configMono,  'issue ',           '',                $issueTest1),
            array($configMono,  'issue 123',        '',                $issue123Test1),
            array($configMulti, '',                 '',                $allRedmines),
            array($configMulti, ' ',                '',                $allRedmines),
            array($configMulti, 'test1',            $apiProjectsTest1, $allActionsTest1),
            array($configMulti, ' test1',           $apiProjectsTest1, $allActionsTest1),
            array($configMulti, ' test1 ',          $apiProjectsTest1, $allActionsTest1),
            array($configMulti, 'test1 ',           $apiProjectsTest1, $allActionsTest1),
            array($configMulti, 'test1 home',       $apiProjectsTest1, $allProjectsTest1Home),
            array($configMulti, 'test1 home ',      $apiProjectsTest1, $allProjectsTest1Home),
            array($configMulti, 'test1 home my',    $apiProjectsTest1, $myProjectsTest1Home),
            array($configMulti, 'test1 home the',   $apiProjectsTest1, $theProjectsTest1Home),
            array($configMulti, 'test1 wiki',       $apiProjectsTest1, $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki ',      $apiProjectsTest1, $allProjectsTest1Wiki),
            array($configMulti, 'test1 wiki my',    $apiProjectsTest1, $myProjectsTest1Wiki),
            array($configMulti, 'test1 wiki the',   $apiProjectsTest1, $theProjectsTest1Wiki),
            array($configMulti, 'test1 issues',     $apiProjectsTest1, $allProjectsTest1Issues),
            array($configMulti, 'test1 issues ',    $apiProjectsTest1, $allProjectsTest1Issues),
            array($configMulti, 'test1 issues my',  $apiProjectsTest1, $myProjectsTest1Issues),
            array($configMulti, 'test1 issues the', $apiProjectsTest1, $theProjectsTest1Issues),
            array($configMulti, 'test1 issue ',     '',                $issueTest1),
            array($configMulti, 'test1 issue 123',  '',                $issue123Test1),
            array($configMulti, 'test2',            $apiProjectsTest2, $allActionsTest2),
            array($configMulti, ' test2',           $apiProjectsTest2, $allActionsTest2),
            array($configMulti, ' test2 ',          $apiProjectsTest2, $allActionsTest2),
            array($configMulti, 'test2 ',           $apiProjectsTest2, $allActionsTest2),
            array($configMulti, 'test2 home',       $apiProjectsTest2, $allProjectsTest2Home),
            array($configMulti, 'test2 home ',      $apiProjectsTest2, $allProjectsTest2Home),
            array($configMulti, 'test2 wiki',       $apiProjectsTest2, $allProjectsTest2Wiki),
            array($configMulti, 'test2 wiki ',      $apiProjectsTest2, $allProjectsTest2Wiki),
            array($configMulti, 'test2 issues',     $apiProjectsTest2, $allProjectsTest2Issues),
            array($configMulti, 'test2 issues ',    $apiProjectsTest2, $allProjectsTest2Issues),
            array($configMulti, 'test2 issue ',     '',                $issueTest2),
            array($configMulti, 'test2 issue 123',  '',                $issue123Test2),
        );
    }

    /**
     * Test method for AlfredWorkflow\RedmineProject class
     *
     * @covers AlfredWorkflow\RedmineProject
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
        $client->expects($this->any())
               ->method('api')
               ->with('project')
               ->willReturn($client);
        $client->expects($this->any())
               ->method('all')
               ->willReturn($apiReturn);

        $redmineProject = new RedmineProject($config, new Workflow(), $client);
        $result         = $redmineProject->run($input);

        $this->assertEquals($expectedResult, $result);
    }
}
