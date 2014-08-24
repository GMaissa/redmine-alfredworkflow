<?php
/**
 * Alfred Workflow Redmine
 *
 * Open a Redmine project page.
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 * @license   OSL-3.0 http://opensource.org/licenses/OSL-3.0
 */

namespace AlfredWorkflow;

use Alfred\Workflow;
use AlfredWorkflow\Redmine\Actions\Exception;
use AlfredWorkflow\Redmine\Storage\Cache;
use AlfredWorkflow\Redmine\Storage\Settings;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
     * @var \AlfredWorkflow\Redmine\Storage\Cache $cache
     */
    protected $cache;

    /**
     * Array of Redmine Client object to communicate with Redmine servers
     * @var array $redmineClient
     */
    protected $redmineClient = array();

    /**
     * Indicates if we are in debug mode
     * @var bool $debug
     */
    protected static $debug = false;

    /**
     * Debug log file
     * @var string $debugFile
     */
    protected static $logFile = '/tmp/rw-debug.log';

    /**
     * Logger object
     * @var mixed $logger
     */
    protected static $logger = false;

    /**
     * Logger handler object
     * @var \Monolog\Handler\AbstractHandler $loggerHandler
     */
    protected static $loggerHandler;

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
        // Need this line to be sure that the php timezone is set before comparing dates
        // Otherwise a notice can be triggered
        date_default_timezone_set('Europe/Paris');

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
        try {
            self::log(sprintf('Running %s action with query: %s', $actionGroup, $query));
            $actionsObj = $this->factory($actionGroup);
            $actionsObj->run(trim($query));
        } catch (Exception $exception) {
            $this->workflow->result(
                array(
                    'title' => $exception->getMessage(),
                    'icon'  => 'assets/icons/warning.png',
                    'valid' => 'no',
                )
            );
        } catch (\Exception $exception) {
            self::log($exception->__toString(), Logger::ERROR);
            $this->workflow->result(
                array(
                    'arg'      => 'http://git.io/OZ_3vA',
                    'title'    => 'An error occured',
                    'subtitle' => 'Please submit an issue on http://git.io/OZ_3vA',
                    'icon'     => 'assets/icons/warning.png',
                    'valid'    => 'yes',
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
        self::log(sprintf('Saving action %s with query: %s', $actionGroup, $query));
        $actionsObj = $this->factory($actionGroup);

        return $actionsObj->save(trim($query));
    }

    /**
     * Instantiate action class
     *
     * @param string $actionGroup action class identifier
     *
     * @throws \Exception if the action class does not exists
     * @return object
     */
    protected function factory($actionGroup)
    {
        $className = "AlfredWorkflow\Redmine\Actions\\" . ucfirst($actionGroup) . 'Action';
        if (!class_exists($className)) {
            throw new \Exception(sprintf('Class %s not found', $className));
        }

        return new $className($this->settings, $this->workflow, $this->cache, $this->redmineClient);
    }

    /**
     * Log a message
     *
     * @param string  $message  message to log
     * @param integer $msgLevel message level
     */
    public static function log($message, $msgLevel = Logger::DEBUG)
    {
        if (!self::$logger) {
            $dateFormat = "Y-m-d H:i:s";
            $output     = "[%datetime%] [%channel%] [%level_name%] %message% \n";
            $level      = Logger::WARNING;
            if (self::$debug) {
                $level = Logger::DEBUG;
            }
            $formatter = new LineFormatter($output, $dateFormat, true);
            if (!self::$loggerHandler) {
                self::$loggerHandler = new StreamHandler(self::$logFile, $level);
            }
            self::$loggerHandler->setFormatter($formatter);

            self::$logger = new Logger('REDMINE WORKFLOW');
            self::$logger->pushHandler(self::$loggerHandler);
        }
        switch ($msgLevel) {
            case Logger::DEBUG:
                self::$logger->addDebug($message);
                break;
            case Logger::INFO:
                self::$logger->addInfo($message);
                break;
            case Logger::WARNING:
                self::$logger->addWarning($message);
                break;
            default:
                self::$logger->addError($message);
                break;
        }
    }

    /**
     * Set Logger object to use
     *
     * @param \Monolog\Handler\AbstractHandler $loggerHandler logger handler
     */
    public function setLoggerHandler(AbstractHandler $loggerHandler)
    {
        self::$loggerHandler = $loggerHandler;
    }

    /**
     * Enable / disable debug mode
     *
     * @param boolean $debug debug mode value
     */
    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }
}
