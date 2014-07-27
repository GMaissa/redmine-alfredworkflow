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
        $configMono       = file_get_contents(__DIR__ . '/../../data/config/mono-server.json');
        $configMulti      = file_get_contents(__DIR__ . '/../../data/config/multi-servers.json');
        $apiProjectsTest1 = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test1.json'),   TRUE);
        $apiProjectsTest2 = json_decode(file_get_contents(__DIR__ . '/../../data/api/projects-test2.json'),   TRUE);
        $allProjectsTest1 = file_get_contents(__DIR__ . '/../../data/results/all-projects-test1.xml');
        $allProjectsTest2 = file_get_contents(__DIR__ . '/../../data/results/all-projects-test2.xml');
        $allRedmines      = file_get_contents(__DIR__ . '/../../data/results/all-redmines.xml');

        return array(
            array($configMono,  '',        $apiProjectsTest1, $allProjectsTest1),
            array($configMono,  ' ',       $apiProjectsTest1, $allProjectsTest1),
            array($configMulti, '',        '',             $allRedmines),
            array($configMulti, ' ',       '',                $allRedmines),
            array($configMulti, 'test1',   $apiProjectsTest1, $allProjectsTest1),
            array($configMulti, ' test1',  $apiProjectsTest1, $allProjectsTest1),
            array($configMulti, ' test1 ', $apiProjectsTest1, $allProjectsTest1),
            array($configMulti, 'test1 ',  $apiProjectsTest1, $allProjectsTest1),
            array($configMulti, 'test2',   $apiProjectsTest2, $allProjectsTest2),
            array($configMulti, ' test2',  $apiProjectsTest2, $allProjectsTest2),
            array($configMulti, ' test2 ', $apiProjectsTest2, $allProjectsTest2),
            array($configMulti, 'test2 ',  $apiProjectsTest2, $allProjectsTest2),
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
