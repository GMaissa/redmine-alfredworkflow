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

namespace AlfredWorkflow\Redmine\Actions;

use Alfred\Workflow;
use AlfredWorkflow\Redmine\Storage\Settings;
use Redmine\Client;

/**
 * Redmine Page actions class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class PageAction extends BaseAction
{
    /**
     * Selected Redmine identifier
     * @var boolean/string $redmineId
     */
    protected $redmineId = false;

    /**
     * Array of Redmine Client object to communicate with Redmine servers
     * @var array $_redmineClient
     */
    protected $redmineClient = array();

    /**
     * List of available actions, and their configuration
     * @var array $_actions
     */
    protected $actions = array(
        'home'   => array(
            'title'    => 'Project homepage',
            'icon'     => 'assets/icons/home.png',
            'valid'    => 'no',
            'method'   => 'handleProjectActions',
            'urlParam' => ''
        ),
        'wiki'   => array(
            'title'     => 'Project wiki page',
            'icon'      => 'assets/icons/wiki.png',
            'valid'     => 'no',
            'method'    => 'handleProjectActions',
            'urlParam'  => '/wiki',
            'subAction' => 'promptWikiPages'
        ),
        'issues' => array(
            'title'    => 'Project issues page',
            'icon'     => 'assets/icons/issues.png',
            'valid'    => 'no',
            'method'   => 'handleProjectActions',
            'urlParam' => '/issues'
        ),
        'issue'  => array(
            'title'  => 'Display issue num',
            'icon'   => 'assets/icons/issue.png',
            'valid'  => 'no',
            'method' => 'getIssueNumber',
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
     * @param \AlfredWorkflow\Redmine\Storage\Settings $settings Settings object
     * @param \Alfred\Workflow                         $workflow Alfred Workflow Api object
     * @param mixed                                    $cache    Workflow Cache object
     * @param mixed                                    $clients  array of Redmine Client objects
     */
    public function __construct(Settings $settings, Workflow $workflow, $cache = false, $clients = false)
    {
        parent::__construct($settings, $workflow, $cache, $clients);

        // Need to allow Client object injection for test purpose
        $this->redmineClient = $clients;

        // Managing cached data at start up
        if ($this->cache) {
            $this->redmineProjectsCache = $this->cache->getData();
        }
        foreach (array_keys($this->settings->getData()) as $redmineId) {
            if (!isset($this->redmineProjectsCache[$redmineId])) {
                $this->loadRedmineData($redmineId);
            }
        }
    }

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
     */
    public function run($query)
    {
        $args = explode(' ', trim($query));

        // If there is no redmine server configured, ask the user to configure one
        if ($this->settings->nbRedmineServers() == 0) {
            $this->promptSetupWorkflow();
            // If there is only one redmine server defined, no need to ask which one to us;
        } elseif ($this->settings->nbRedmineServers() == 1) {
            $this->redmineId = $this->settings->getDefaultRedmineId();
            $params          = $args;
            $this->promptRedmineActions($params);
        } else {
            $pattern = trim($args[0]);
            array_shift($args);
            $params = $args;

            if ($this->settings->hasDataForKey($pattern)) {
                $this->redmineId = $pattern;
                $this->promptRedmineActions($params);
            } else {
                $this->promptRedmines($pattern);
            }
        }
    }

    /**
     * Handle project actions
     *
     * display the project list or a project wiki pages
     *
     * @param array $params workflow parameters
     *                      first param should be the action identifier and
     *                      second one should be the project identifier or the issue id
     */
    protected function handleProjectActions($params)
    {
        $projectPattern = array_key_exists(1, $params) ? trim($params[1]) : '';
        $subAction      = $this->getActionParam('subAction');

        if ($subAction &&
            $projectPattern &&
            array_key_exists($projectPattern, $this->getProjectsData($projectPattern))
        ) {
            $wikiPattern = array_key_exists(2, $params) ? trim($params[2]) : '';
            $this->promptWikiPages($projectPattern, $wikiPattern);
        } else {
            $autocomplete = $subAction ? true : false;
            $this->promptProjects($projectPattern, $autocomplete);
        }
    }

    /**
     * Prompt the user to setup workflow
     */
    protected function promptSetupWorkflow()
    {
        $this->workflow->result(
            array(
                'title'    => 'No redmine server configuration',
                'subtitle' => 'Please use "red config" key to setup the workflow',
                'icon'     => 'assets/icons/config.png',
                'valid'    => 'no',
            )
        );
    }

    /**
     * Retrieve and format project informations for Alfred
     *
     * @param string  $projectPattern project matching pattern
     * @param boolean $autocomplete   define if the "projects" action is the last one or not
     */
    protected function promptProjects($projectPattern, $autocomplete = false)
    {
        $redmine      = $this->settings->getDataForKey($this->redmineId);
        $redmineParam = ($this->settings->nbRedmineServers() > 1) ? $this->redmineId . ' ' : '';
        $config       = $this->actions[$this->action];

        $projects = array_slice($this->getProjectsData($projectPattern), 0, 10);
        foreach ($projects as $identifier => $project) {
            $result = array(
                'uid'      => $identifier,
                'arg'      => $redmine['url'] . '/projects/' . $identifier . $this->getActionParam('urlParam'),
                'title'    => $project['name'],
                'subtitle' => substr($project['description'], 0, 50),
                'icon'     => $config['icon'],
                'valid'    => 'yes'
            );
            if ($autocomplete) {
                $result['arg']          = '';
                $result['valid']        = 'no';
                $result['autocomplete'] = sprintf('%s%s %s ', $redmineParam, $this->action, $identifier);
            }

            $this->workflow->result($result);
        }
    }

    /**
     * Retrieve and format project wiki pages for Alfred
     *
     * @param string $projectId   project identifier
     * @param string $wikiPattern wiki page identification pattern
     */
    protected function promptWikiPages($projectId, $wikiPattern)
    {
        $redmine   = $this->settings->getDataForKey($this->redmineId);
        $apiResult = $this->getRedmineClient($this->redmineId)->api('wiki')->all($projectId);
        $config    = $this->actions['wiki'];

        // Check if there are results
        if (isset($apiResult['wiki_pages'])) {
            foreach ($apiResult['wiki_pages'] as $wikiPage) {
                // If the title of the page matches the search pattern provided
                if (!$wikiPattern || preg_match('/' . strtolower($wikiPattern) . '/', strtolower($wikiPage['title']))) {
                    $pageTitle = $wikiPage['title'];
                    if (isset($wikiPage['parent'])) {
                        $pageTitle = $wikiPage['parent']['title'] . ' \ ' . $pageTitle;
                    }
                    $result = array(
                        'arg'   => $redmine['url'] . '/projects/' . $projectId . '/wiki/' . $wikiPage['title'],
                        'title' => $pageTitle,
                        'icon'  => $config['icon'],
                        'valid' => 'yes'
                    );
                    $this->workflow->result($result);
                }
            }
        } else {
            $result = array(
                'title' => 'No wiki pages for project ' . $projectId,
                'icon'  => $config['icon'],
                'valid' => 'no'
            );
            $this->workflow->result($result);
        }
    }

    /**
     * Retrieve and format Redmine plateforms informations for Alfred
     *
     * @param string $redminePattern redmine identifier pattern
     */
    protected function promptRedmines($redminePattern)
    {
        foreach ($this->settings->getData() as $redKey => $redData) {
            if ($redminePattern == '' || preg_match('/' . $redminePattern . '/', $redKey)) {
                $this->workflow->result(
                    array(
                        'title'        => $redData['name'],
                        'icon'         => 'assets/icons/redmine.png',
                        'valid'        => 'no',
                        'autocomplete' => sprintf('%s ', $redKey)
                    )
                );
            }
        }
    }

    /**
     * Retrieve available workflow actions
     *
     * @param array $params workflow parameters
     *                      first param should be the action identifier and
     *                      second one should be the project identifier or the issue id
     */
    protected function promptRedmineActions($params)
    {
        $actionPattern = array_key_exists(0, $params) ? $params[0] : false;

        if ($actionPattern && array_key_exists($actionPattern, $this->actions)) {
            $this->action = $actionPattern;
            call_user_func(
                array(
                    $this,
                    $this->getActionParam('method')
                ),
                $params
            );
        } else {
            $redmineParam = ($this->settings->nbRedmineServers() > 1) ? $this->redmineId . ' ' : '';
            foreach ($this->actions as $action => $params) {
                if (!$actionPattern || preg_match('/' . $actionPattern . '/', $action)) {
                    $this->workflow->result(
                        array(
                            'title'        => $params['title'],
                            'icon'         => $params['icon'],
                            'valid'        => $params['valid'],
                            'autocomplete' => sprintf('%s%s ', $redmineParam, $action)
                        )
                    );
                }
            }
        }
    }

    /**
     * Manage display
     *
     * @param array $params workflow parameters, first value should be the issue number
     */
    protected function getIssueNumber($params)
    {
        $redmine     = $this->settings->getDataForKey($this->redmineId);
        $issueNumber = array_key_exists(1, $params) ? $params[1] : '';
        $config      = $this->actions['issue'];
        $this->workflow->result(
            array(
                'uid'      => '',
                'arg'      => $redmine['url'] . '/issues/' . $issueNumber,
                'title'    => 'Display issue num ' . $issueNumber,
                'subtitle' => '',
                'icon'     => $config['icon'],
                'valid'    => 'yes'
            )
        );
    }

    /**
     * Retrieve projects data from Redmine
     *
     * @param string $identifierPattern project identifier matching pattern
     *
     * @return mixed
     */
    protected function getProjectsData($identifierPattern = null)
    {
        $redmineId = $this->redmineId;
        if (!array_key_exists($redmineId, $this->redmineProjectsCache)) {
            // @codeCoverageIgnoreStart
            $this->loadRedmineData($redmineId);
        }
        // @codeCoverageIgnoreEnd

        $matchingResults = $this->redmineProjectsCache[$redmineId];
        if ($identifierPattern) {
            $matchingResults = array_filter(
                $this->redmineProjectsCache[$redmineId],
                function ($project) use ($identifierPattern) {
                    return preg_match('/' . $identifierPattern . '/', $project['identifier']);
                }
            );
        }

        return $matchingResults;
    }

    /**
     * Get the Redmine Client object used to call Redmine API
     *
     * @param string $redmineId redmine server identifier
     *
     * @return \Redmine\Client
     */
    public function getRedmineClient($redmineId)
    {
        // Need to allow Client object injection for test purpose
        if (!isset($this->redmineClient[$redmineId])) {
            // @codeCoverageIgnoreStart
            $redmine                         = $this->settings->getDataForKey($redmineId);
            $this->redmineClient[$redmineId] = new Client($redmine['url'], $redmine['api-key']);
        }

        // @codeCoverageIgnoreEnd

        return $this->redmineClient[$redmineId];
    }

    /**
     * Retrieve data (project details) from Redmine server
     *
     * @param string $redmineId redmine server identifier
     */
    protected function loadRedmineData($redmineId)
    {
        $limit                                  = 100;
        $page                                   = 0;
        $this->redmineProjectsCache[$redmineId] = array();
        do {
            $result = $this->getRedmineClient($redmineId)->api('project')->all(
                array(
                    'limit'  => $limit,
                    'offset' => $page * $limit
                )
            );
            foreach ($result['projects'] as $project) {
                $this->redmineProjectsCache[$redmineId][$project['identifier']] = $project;
            }
            $page++;
        } while (count($this->redmineProjectsCache[$redmineId]) < $result['total_count']);

        // Need this line to be sure that the php timezone is set before comparing dates
        // Otherwise a notice can be triggered
        date_default_timezone_set('Europe/Paris');
        uasort(
            $this->redmineProjectsCache[$redmineId],
            function ($first, $second) {
                return strtotime($second['updated_on']) - strtotime($first['updated_on']);
            }
        );

        if ($this->cache) {
            $this->cache->setData($this->redmineProjectsCache)->save();
        }
    }
}
