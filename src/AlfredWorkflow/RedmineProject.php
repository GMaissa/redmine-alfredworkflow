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
    protected $_config= false;

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
     * Redmine key matching pattern for autocompletion
     * @var mixed $_redmineMatch
     */
    protected $_redmineMatch = null;

    /**
     * Project identifier matching pattern for autocompletion
     * @var mixed $_projectMatch
     */
    protected $_projectMatch = null;

    /**
     * Class constructor
     *
     * @param string   $config   workflow configuration
     * @param Workflow $workflow Alfred Workflow Api object
     * @param mixed    $client   Redmine Client object
     */
    public function __construct($config, Workflow $workflow, $client = false)
    {
        $this->_config        = $config;
        $this->_workflow      = $workflow;
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
        $redmines    = json_decode($this->_config, TRUE);
        $redmineKeys = array_keys($redmines);

        // If there is only one redmine plateform defined, no need to ask which one to use
        if (count($redmines) == 1) {
            $this->_projectMatch = trim($query);
            return $this->_getProjects($redmines[$redmineKeys[0]]);
        } else {
            $args                = explode(' ', trim($query));
            $this->_redmineMatch = trim($args[0]);

            if (array_key_exists($args[0], $redmines)) {
                $this->_projectMatch = trim($args[0]);
                return $this->_getProjects($redmines[$this->_redmineMatch]);
            } else {
                return $this->_getRedmines($redmines);
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
        // Need to allow Client object injection for test purpose
        if (!$this->_redmineClient) {
            // @codeCoverageIgnoreStart
            $this->_redmineClient = new Client($redmine['url'], $redmine['api-key']);
        }
        // @codeCoverageIgnoreEnd

        $projects    = $this->_redmineClient->api('project')->all();
        $resultArray = array();
        foreach ($projects['projects'] as $project) {
            if ($this->_projectMatch == '' || preg_match('/' . $this->_projectMatch . '/', $project['identifier'])) {
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
            if ($this->_redmineMatch == '' || preg_match('/'.trim($this->_redmineMatch).'/', $redKey)) {
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
