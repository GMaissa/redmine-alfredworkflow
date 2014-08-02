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

namespace AlfredWorkflow;

use Alfred\Workflow;
use Redmine\Client;

/**
 * Redmine Project class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class RedmineProject
{
    /**
     * Indicates if we are in debug mode
     * @var bool $_debug
     */
    protected $debug = true;

    /**
     * Workflow configuration
     * @var string $_config
     */
    protected $config = false;

    /**
     * Selected Redmine identifier
     * @var boolean/string $redmineId
     */
    protected $redmineId = false;

    /**
     * Selected workflow action
     * @var boolean/string $action
     */
    protected $action = false;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $_workflow
     */
    protected $workflow;

    /**
     * Redmine Client object to communicate with Redmine servers
     * @var \Redmine\Client $_redmineClient
     */
    protected $redmineClient;

    /**
     * List of available actions, and their configuration
     * @var array $_actions
     */
    protected $actions = array(
        'home'   => array(
            'title'    => 'Project homepage',
            'method'   => 'handleProjectActions',
            'urlParam' => ''
        ),
        'wiki'   => array(
            'title'     => 'Project wiki page',
            'method'    => 'handleProjectActions',
            'urlParam'  => '/wiki',
            'subAction' => 'promptWikiPages'
        ),
        'issues' => array(
            'title'    => 'Project issues page',
            'method'   => 'handleProjectActions',
            'urlParam' => '/issues'
        ),
        'issue'  => array(
            'title'  => 'Display issue num',
            'method' => 'getIssueNumber'
        )
    );

    /**
     * List of projects per redmine
     * @var array $_redmineProjectsCache
     */
    protected $redmineProjectsCache = array();

    /**
     * Class constructor
     *
     * @param string   $config   workflow configuration
     * @param Workflow $workflow Alfred Workflow Api object
     * @param mixed    $client   Redmine Client object
     */
    public function __construct($config, Workflow $workflow, $client = false)
    {
        $this->config   = json_decode($config, true);
        $this->workflow = $workflow;
        // Need to allow Client object injection for test purpose
        $this->redmineClient = $client;
    }

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
     *
     * @return string
     */
    public function run($query)
    {
        $redmineKeys = array_keys($this->config);
        $args        = explode(' ', trim($query));

        // If there is only one redmine plateform defined, no need to ask which one to use
        if (count($this->config) == 1) {
            $this->redmineId = $redmineKeys[0];
            $params          = $args;
            $return          = $this->dispatchAction($params);
        } else {
            $redminePattern = trim($args[0]);
            array_shift($args);
            $params = $args;

            if (array_key_exists($redminePattern, $this->config)) {
                $this->redmineId = $redminePattern;
                $return          = $this->dispatchAction($params);
            } else {
                $return = $this->promptRedmines($redminePattern);
            }
        }

        return $return;
    }

    /**
     * Manage actions
     *
     * @param array $params workflow parameters
     *                      first param should be the action identifier and
     *                      second one should be the project identifier or the issue id
     *
     * @return string
     */
    protected function dispatchAction($params)
    {
        $actionPattern = array_key_exists(0, $params) ? $params[0] : false;

        if ($actionPattern && array_key_exists($actionPattern, $this->actions)) {
            $this->action = $actionPattern;
            if ($this->getActionParam('method') == 'getIssueNumber') {
                $issueNumber = array_key_exists(1, $params) ? $params[1] : '';
                $return      = $this->getIssueNumber($issueNumber);
            } elseif ($this->getActionParam('method') == 'handleProjectActions') {
                $return = $this->handleProjectActions($params);
            }
        } else {
            $return = $this->promptRedmineActions($actionPattern);
        }

        return $return;
    }

    /**
     * Handle project actions
     *
     * display the project list or a project wiki pages
     *
     * @param array $params workflow parameters
     *                      first param should be the action identifier and
     *                      second one should be the project identifier or the issue id
     *
     * @return string
     */
    protected function handleProjectActions($params)
    {
        $projects       = $this->getProjectsData();
        $projectPattern = array_key_exists(1, $params) ? trim($params[1]) : '';
        $subAction      = $this->getActionParam('subAction');

        if ($subAction && $projectPattern && array_key_exists($projectPattern, $projects)) {
            $wikiPattern = array_key_exists(2, $params) ? trim($params[2]) : '';
            $return      = $this->promptWikiPages($projectPattern, $wikiPattern);
        } else {
            $autocomplete = $subAction ? true : false;
            $return       = $this->promptProjects($projectPattern, $autocomplete);
        }

        return $return;
    }

    /**
     * Retrieve and format project informations for Alfred
     *
     * @param string  $projectPattern project matching pattern
     * @param boolean $autocomplete   define if the "projects" action is the last one or not
     *
     * @return string
     */
    protected function promptProjects($projectPattern, $autocomplete = false)
    {
        $redmine      = $this->config[$this->redmineId];
        $projects     = $this->getProjectsData();
        $redmineParam = (count($this->config) > 1) ? ' ' . $this->redmineId : '';

        foreach ($projects as $identifier => $project) {
            // If the project identifier matches the search pattern provided
            if (!$projectPattern || preg_match('/' . $projectPattern . '/', $identifier)) {
                $result = array(
                    'uid'      => $identifier,
                    'arg'      => $redmine['url'] . '/projects/' . $identifier . $this->getActionParam('urlParam'),
                    'title'    => $project['name'],
                    'subtitle' => $project['description'] ? substr($project['description'], 0, 50) : '-',
                    'icon'     => 'icon.png',
                    'valid'    => 'yes'
                );
                if ($autocomplete) {
                    $result['arg']          = '';
                    $result['valid']        = 'no';
                    $result['autocomplete'] = sprintf('%s %s %s ', $redmineParam, $this->action, $identifier);
                }

                $this->workflow->result($result);
            }
        }

        return $this->workflow->toXML();
    }

    /**
     * Retrieve and format project wiki pages for Alfred
     *
     * @param string $projectId   project identifier
     * @param string $wikiPattern wiki page identification pattern
     *
     * @return string
     */
    protected function promptWikiPages($projectId, $wikiPattern)
    {
        $redmine   = $this->config[$this->redmineId];
        $apiResult = $this->getRedmineClient()->api('wiki')->all($projectId);

        // Check if there are results
        if (is_array($apiResult) && array_key_exists('wiki_pages', $apiResult) && count($apiResult)) {
            foreach ($apiResult['wiki_pages'] as $wikiPage) {
                // If the title of the page matches the search pattern provided
                if (!$wikiPattern || preg_match('/' . strtolower($wikiPattern) . '/', strtolower($wikiPage['title']))) {
                    $pageTitle = $wikiPage['title'];
                    if (array_key_exists('parent', $wikiPage)) {
                        $pageTitle = $wikiPage['parent']['title'] . ' \ ' . $pageTitle;
                    }
                    $result = array(
                        'uid'      => '',
                        'arg'      => $redmine['url'] . '/projects/' . $projectId . '/wiki/' . $wikiPage['title'],
                        'title'    => $pageTitle,
                        'subtitle' => '',
                        'icon'     => 'icon.png',
                        'valid'    => 'yes'
                    );
                    $this->workflow->result($result);
                }
            }
        } else {
            $result = array(
                'uid'      => '',
                'arg'      => '',
                'title'    => 'No wiki pages for project ' . $projectId,
                'subtitle' => '',
                'icon'     => 'icon.png',
                'valid'    => 'no'
            );
            $this->workflow->result($result);
        }

        return $this->workflow->toXML();
    }

    /**
     * Retrieve and format Redmine plateforms informations for Alfred
     *
     * @param string $redminePattern redmine identifier pattern
     *
     * @return string
     */
    protected function promptRedmines($redminePattern)
    {
        foreach ($this->config as $redKey => $redData) {
            if ($redminePattern == '' || preg_match('/' . $redminePattern . '/', $redKey)) {
                $this->workflow->result(
                    array(
                        'uid'          => '',
                        'arg'          => '',
                        'title'        => $redData['name'],
                        'subtitle'     => '',
                        'icon'         => 'icon.png',
                        'valid'        => 'no',
                        'autocomplete' => sprintf(' %s ', $redKey)
                    )
                );
            }
        }

        return $this->workflow->toXML();
    }

    /**
     * Retrieve available workflow actions
     *
     * @param string $actionPattern action identifier pattern
     *
     * @return string
     */
    protected function promptRedmineActions($actionPattern)
    {
        $redmineParam = (count($this->config) > 1) ? ' ' . $this->redmineId : '';
        foreach ($this->actions as $action => $params) {
            if (!$actionPattern || preg_match('/' . $actionPattern . '/', $action)) {
                $this->workflow->result(
                    array(
                        'uid'          => '',
                        'arg'          => '',
                        'title'        => $params['title'],
                        'subtitle'     => '',
                        'icon'         => 'icon.png',
                        'valid'        => 'no',
                        'autocomplete' => sprintf('%s %s ', $redmineParam, $action)
                    )
                );
            }
        }

        return $this->workflow->toXML();
    }

    /**
     * Manage display
     *
     * @param string $issueNumber id of the issue to display
     *
     * @return string
     */
    protected function getIssueNumber($issueNumber)
    {
        $this->workflow->result(
            array(
                'uid'          => '',
                'arg'          => $this->config[$this->redmineId]['url'] . '/issues/' . $issueNumber,
                'title'        => 'Display issue num ' . $issueNumber,
                'subtitle'     => '',
                'icon'         => 'icon.png',
                'valid'        => 'yes',
                'autocomplete' => ''
            )
        );

        return $this->workflow->toXML();
    }

    /**
     * Retrieve projects data from Redmine
     *
     * @return mixed
     */
    protected function getProjectsData()
    {
        $redmineId = $this->redmineId;
        if (!array_key_exists($redmineId, $this->redmineProjectsCache)) {
            $projects = $this->getRedmineClient()->api('project')->all();
            foreach ($projects['projects'] as $project) {
                $this->redmineProjectsCache[$redmineId][$project['identifier']] = $project;
            }
        }

        return $this->redmineProjectsCache[$redmineId];
    }

    /**
     * Get the Redmine Client object used to call Redmine API
     *
     * @return \Redmine\Client
     */
    public function getRedmineClient()
    {
        // Need to allow Client object injection for test purpose
        if (!$this->redmineClient) {
            // @codeCoverageIgnoreStart
            $redmine             = $this->config[$this->redmineId];
            $this->redmineClient = new Client($redmine['url'], $redmine['api-key']);
        }

        // @codeCoverageIgnoreEnd

        return $this->redmineClient;
    }

    /**
     * Retrive configuration param for the current action
     *
     * @param string $param action parameter
     *
     * @return mixed
     */
    protected function getActionParam($param)
    {
        return array_key_exists($param, $this->actions[$this->action]) ? $this->actions[$this->action][$param] : false;
    }
}
