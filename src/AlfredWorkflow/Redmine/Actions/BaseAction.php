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
use AlfredWorkflow\Redmine;
use AlfredWorkflow\Redmine\Storage\Settings;
use AlfredWorkflow\Redmine\Storage\Cache;
use Monolog\Logger;

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
     * @var \AlfredWorkflow\Redmine\Storage\Cache $cache
     */
    protected $cache;

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
     * @param \AlfredWorkflow\Redmine\Storage\Cache    $cache    Workflow Cache object
     * @param array                                    $clients  array of Redmine Client objects
     */
    public function __construct(Settings $settings, Workflow $workflow, Cache $cache, $clients = array())
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

    /**
     * Throw an exception that will be catched by the \AlfredWorkflow\Redmine object
     * to display the error message
     *
     * @param string  $message  message to log
     * @param string  $method   method name that requested to throw the exception
     * @param integer $logLevel log level
     *
     * @throws Exception
     */
    protected function throwException($message, $method, $logLevel = Logger::DEBUG)
    {
        Redmine::log(sprintf('%s: %s', $method, $message), $logLevel);
        throw new Exception($message);
    }

    /**
     * Extract data from array
     *
     * @param array  $array array from which to extract data
     * @param string $key   array key to extract
     *
     * @return mixed
     */
    protected function extractDataFromArray($array, $key)
    {
        return array_key_exists($key, $array) ? $array[$key] : '';
    }

    /**
     * Add a result to display if the identifier matches the search pattern
     *
     * @param string $identifier result identifier
     * @param array  $params     result parameters
     * @param mixed  $pattern    identifier matching pattern
     */
    protected function addResult($identifier, $params, $pattern = null)
    {
        if (preg_match('/' . strtolower($pattern) . '/', strtolower($identifier))) {
            $this->workflow->result($params);
        }
    }
}
