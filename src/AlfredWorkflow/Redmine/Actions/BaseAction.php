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
     * Indicates if we are in debug mode
     * @var bool $debug
     */
    protected $debug = true;

    /**
     * Workflow settings
     * @var string $settings
     */
    protected $settings = false;

    /**
     * Workflow cache management object
     * @var mixed $cache
     */
    protected $cache = false;

    /**
     * Selected workflow action
     * @var boolean/string $action
     */
    protected $action = false;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $workflow
     */
    protected $workflow;

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
     * @param mixed                                    $clients  array of Redmine Client objects
     */
    public function __construct(Settings $settings, Workflow $workflow, $cache = false, $clients = false)
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
