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

/**
 * Redmine Page actions class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
abstract class BaseAction
{
    /**
     * Workflow settings
     * @var \AlfredWorkflow\Redmine\Storage\Settings $settings
     */
    protected $settings;

    /**
     * Workflow cache management object
     * @var mixed $cache
     */
    protected $cache = false;

    /**
     * Selected workflow action
     * @var mixed $action
     */
    protected $action = false;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $workflow
     */
    protected $workflow;

    /**
     * Array of Redmine Client object to communicate with Redmine servers
     * @var array $redmineClient
     */
    protected $redmineClient = array();

    /**
     * List of available actions, and their configuration
     * @var array $actions
     */
    protected $actions = array();

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
        return isset($this->actions[$this->action][$param]) ? $this->actions[$this->action][$param] : false;
    }
}
