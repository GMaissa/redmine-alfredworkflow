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

Use \Redmine,
    \Alfred\Workflow;

class RedmineProject
{
    /**
     * Workflow configuration
     * @var string $_config
     */
    protected $_config= false;

    /**
     * Workflow onject to format data for alfred
     * @var \Alfred\Workflow $_workflow
     */
    protected $_workflow;

    /**
     * Redmine key matching pattern for autocompletion
     * @var mixed $_redminePattern
     */
    protected $_redminePattern = null;

    /**
     * Project identifier matching pattern for autocompletion
     * @var mixed $_projectPattern
     */
    protected $_projectPattern = null;

    /**
     * Class constructor
     *
     * @param string $config workflow configuration
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->_config   = $config;
        $this->_workflow = new Workflow('com.gmaissa.redmine-workflow');
    }

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
     */
    public function run($query)
    {
        $redmines    = json_decode($this->_config, TRUE);
        $redmineKeys = array_keys($redmines);

        // If there is only one redmine plateform defined, no need to ask which one to use
        if (count($redmines) == 1) {
            $this->_projectPattern = trim($query);
            echo $this->_getProjects($redmines[$redmineKeys[0]]);
        } else {
            $args                  = explode(' ', trim($query));
            $this->_redminePattern = trim($args[0]);

            if (array_key_exists($args[0], $redmines)) {
                $this->_projectPattern = trim($args[1]);
                echo $this->_getProjects($redmines[$this->_redminePattern]);
            } else {
                echo $this->_getRedmines($redmines);
            }
        }
    }

    /**
     * Retrieve and format project informations for Alfred
     *
     * @param array $redmine redmine plateform configuration
     *
     * @return string
     */
    function _getProjects($redmine)
    {
        $client   = new Redmine\Client($redmine['url'], $redmine['api-key']);
        $projects = $client->api('project')->all();
        $resultArray = array();
        foreach ($projects['projects'] as $project) {
            if ($this->_projectPattern == '' || preg_match('/'.trim($this->_projectPattern).'/', $project['identifier'])) {
                $this->_workflow->result(
                    $resultArray[] = array(
                        'uid'      => $project['identifier'],
                        'arg'      => $redmine['url'] . '/projects/' . $project['identifier'],
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
     * @param array $redmines redmine plateforms informations
     *
     * @return string
     */
    protected function _getRedmines($redmines)
    {
        foreach ($redmines as $redKey => $redData) {
            if ($this->_redminePattern == '' || preg_match('/'.trim($this->_redminePattern).'/', $redKey)) {
                $this->_workflow->result(
                    $resultArray[] = array(
                        'uid'          => '',
                        'arg'          => '',
                        'title'        => $redData['name'],
                        'subtitle'     => '',
                        'icon'         => 'icon.png',
                        'valid'        => 'no',
                        'autocomplete' => ' ' . $redKey
                    )
                );
            }
        }

        return $this->_workflow->toXML();
    }
}
