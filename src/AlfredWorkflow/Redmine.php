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
use AlfredWorkflow\Redmine\Storage\Settings;

/**
 * Redmine Project class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class Redmine
{
    /**
     * Workflow settings
     * @var \AlfredWorkflow\Redmine\Storage\Settings $settings
     */
    protected $settings;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $workflow
     */
    protected $workflow;

    /**
     * Workflow cache management object
     * @var mixed $cache
     */
    protected $cache = false;

    /**
     * Array of Redmine Client object to communicate with Redmine servers
     * @var array $redmineClient
     */
    protected $redmineClient = array();

    /**
     * Class constructor
     *
     * @param \AlfredWorkflow\Redmine\Storage\Settings $settings Settings object
     * @param \Alfred\Workflow                         $workflow Alfred Workflow Api object
     * @param mixed                                    $cache    Workflow Cache object
     * @param array                                    $clients  array of Redmine Client objects
     */
    public function __construct(Settings $settings, Workflow $workflow, $cache = false, $clients = array())
    {
        $this->settings = $settings;
        $this->workflow = $workflow;
        $this->cache    = $cache;
        // Need to allow Client object injection for test purpose
        $this->redmineClient = $clients;

        //Load settings
        $this->settings->load();

        // Managing cached data at start up
        if ($this->cache) {
            $this->cache->load();
        }
    }

    /**
     * Run the workflow
     *
     * @param string $actionGroup Identifier of the actions class to run
     * @param string $query       Alfred query string
     *
     * @return string
     */
    public function run($actionGroup, $query)
    {
        try{
            $actionsObj = $this->factory($actionGroup);
            $actionsObj->run($query);
        } catch (AlfredWorkflow\Redmine\Actions\Exception $exception) {
            $this->workflow->result(
                array(
                    'title'    => 'No action for this query',
                    'icon'     => 'assets/icons/error.png',
                    'valid'    => 'no',
                )
            );
        }

        return $this->workflow->toXML();
    }

    /**
     * Save action
     *
     * @param string $actionGroup Identifier of the actions class to run
     * @param string $query       Alfred query string
     *
     * @return string
     */
    public function save($actionGroup, $query)
    {
        $actionsObj = $this->factory($actionGroup);

        return $actionsObj->save($query);
    }

    /**
     * Instanciate action class
     *
     * @param string $actionGroup action class identifier
     *
     * @return object
     */
    protected function factory($actionGroup)
    {
        $className = "AlfredWorkflow\Redmine\Actions\\" . ucfirst($actionGroup) . 'Action';

        return new $className($this->settings, $this->workflow, $this->cache, $this->redmineClient);
    }
}
