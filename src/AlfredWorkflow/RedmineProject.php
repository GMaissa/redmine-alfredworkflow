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

Namespace AlfredWorkflow;

Use Redmine\Client,
    Alfred\Workflow;

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
     * Workflow configuration
     * @var string $_config
     */
    protected $_config = false;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $_workflow
     */
    protected $_workflow;

    /**
     * Redmine Client object to communicate with Redmine servers
     * @var \Redmine\Client $_redmineClient
     */
    protected $_redmineClient;

    /**
     * List of available actions, and their configuration
     * @var array $_actions
     */
    protected $_actions = array(
        'home'   => array(
            'title'    => 'Project homepage',
            'method'   => 'getProjects',
            'urlParam' => ''
        ),
        'wiki'   => array(
            'title'    => 'Project wiki page',
            'method'   => 'getProjects',
            'urlParam' => '/wiki'
        ),
        'issues' => array(
            'title'    => 'Project issues page',
            'method'   => 'getProjects',
            'urlParam' => '/issues'
        ),
        'issue'  => array(
            'title'  => 'Display issue num',
            'method' => 'getIssueNumber'
        )
    );

    /**
     * Class constructor
     *
     * @param string   $config   workflow configuration
     * @param Workflow $workflow Alfred Workflow Api object
     * @param mixed    $client   Redmine Client object
     */
    public function __construct($config, Workflow $workflow, $client = false)
    {
        $this->_config   = json_decode($config, true);
        $this->_workflow = $workflow;
        // Need to allow Client object injection for test purpose
        $this->_redmineClient = $client;
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
        $redmineKeys = array_keys($this->_config);
        $args        = explode(' ', trim($query));

        // If there is only one redmine plateform defined, no need to ask which one to use
        if (count($this->_config) == 1) {
            $params = $args;
            $return = $this->dispatchAction($redmineKeys[0], $params);
        } else {
            $redmineId = trim($args[0]);
            array_shift($args);
            $params = $args;

            if (array_key_exists($redmineId, $this->_config)) {
                $return = $this->dispatchAction($redmineId, $params);
            } else {
                $return = $this->getRedmines($redmineId);
            }
        }

        return $return;
    }

    /**
     * Manage actions
     *
     * @param string $redmineId redmine server identifier
     * @param array  $params    workflow parameters
     *                          first param should be the action identifier and
     *                          second one should be the project identifier or the issue id
     *
     * @return string
     */
    protected function dispatchAction($redmineId, $params)
    {
        $action = array_key_exists(0, $params) ? $params[0] : false;

        if ($action && array_key_exists($action, $this->_actions)) {
            if ($this->_actions[$action]['method'] == 'getIssueNumber') {
                $issueNumber = array_key_exists(1, $params) ? $params[1] : '';
                $return      = $this->getIssueNumber($redmineId, $issueNumber);
            } else if ($this->_actions[$action]['method'] == 'getProjects') {
                $projectPattern = array_key_exists(1, $params) ? trim($params[1]) : '';
                $return         = $this->getProjects($redmineId, $action, $projectPattern);
            }
        } else {
            $return = $this->getRedmineActions($redmineId, $action);
        }

        return $return;
    }

    /**
     * Retrieve and format project informations for Alfred
     *
     * @param array  $redmineId      redmine identifier
     * @param string $action         action identifier
     * @param string $projectPattern project matching pattern
     *
     * @return string
     */
    function getProjects($redmineId, $action, $projectPattern)
    {
        $redmine = $this->_config[$redmineId];

        // Need to allow Client object injection for test purpose
        if (!$this->_redmineClient) {
            // @codeCoverageIgnoreStart
            $this->_redmineClient = new Client($redmine['url'], $redmine['api-key']);
        }
        // @codeCoverageIgnoreEnd

        $projects    = $this->_redmineClient->api('project')->all();
        $resultArray = array();
        foreach ($projects['projects'] as $project) {
            if (!$projectPattern || preg_match('/' . $projectPattern . '/', $project['identifier'])) {
                $this->_workflow->result(
                    array(
                        'uid'      => $project['identifier'],
                        'arg'      => $redmine['url'] . '/projects/' . $project['identifier'] .
                                      $this->_actions[$action]['urlParam'],
                        'title'    => $project['name'],
                        'subtitle' => $project['description'] ? substr($project['description'], 50) : '-',
                        'icon'     => 'icon.png',
                        'valid'    => 'yes'
                    )
                );
            }
        }

        return $this->_workflow->toXML();
    }

    /**
     * Retrieve and format Redmine plateforms informations for Alfred
     *
     * @param string $redminePattern redmine identifier pattern
     *
     * @return string
     */
    protected function getRedmines($redminePattern)
    {
        foreach ($this->_config as $redKey => $redData) {
            if ($redminePattern == '' || preg_match('/' . $redminePattern . '/', $redKey)) {
                $this->_workflow->result(
                    array(
                        'uid'          => '',
                        'arg'          => '',
                        'title'        => $redData['name'],
                        'subtitle'     => '',
                        'icon'         => 'icon.png',
                        'valid'        => 'no',
                        'autocomplete' => ' ' . $redKey . ' '
                    )
                );
            }
        }

        return $this->_workflow->toXML();
    }

    /**
     * Retrieve available workflow actions
     *
     * @param string $redmineId     redmine identifier
     * @param string $actionPattern action identifier pattern
     *
     * @return string
     */
    protected function getRedmineActions($redmineId, $actionPattern)
    {
        $redmineParam = (count($this->_config) > 1) ? $redmineId . ' ' : '';
        foreach ($this->_actions as $action => $params) {
            if (!$actionPattern || preg_match('/' . $actionPattern . '/', $action)) {
                $this->_workflow->result(
                    array(
                        'uid'          => '',
                        'arg'          => '',
                        'title'        => $params['title'],
                        'subtitle'     => '',
                        'icon'         => 'icon.png',
                        'valid'        => 'no',
                        'autocomplete' => ' ' . $redmineParam . $action . ' '
                    )
                );
            }
        }

        return $this->_workflow->toXML();
    }

    /**
     * Manage display
     *
     * @param string $redmineId   redmine identifier
     * @param string $issueNumber id of the issue to display
     *
     * @return string
     */
    protected function getIssueNumber($redmineId, $issueNumber)
    {
        $this->_workflow->result(
            array(
                'uid'          => '',
                'arg'          => $this->_config[$redmineId]['url'] . '/issues/' . $issueNumber,
                'title'        => 'Display issue num ' . $issueNumber,
                'subtitle'     => '',
                'icon'         => 'icon.png',
                'valid'        => 'yes',
                'autocomplete' => ''
            )
        );

        return $this->_workflow->toXML();
    }
}
