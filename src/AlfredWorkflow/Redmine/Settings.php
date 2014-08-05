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

namespace AlfredWorkflow\Redmine;

use Camspiers\JsonPretty\JsonPretty;

/**
 * Redmine Settings class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class Settings
{
    /**
     * Workflow configuration
     * @var string $_config
     */
    protected $config = array();

    /**
     * Workflow configuration file path
     * @var string $configFile
     */
    protected $configFile = './config/settings.json';

    /**
     * Class constructor
     *
     * @param bool /string $filename
     */
    public function __construct($filename = false)
    {
        if ($filename) {
            $this->configFile = $filename;
        }
        if (file_exists($this->configFile) && file_get_contents($this->configFile) != '') {
            $this->config = json_decode(file_get_contents($this->configFile), true);
        }
    }

    /**
     * Retrieve number of Redmine servers configured
     *
     * @return integer
     */
    public function nbRedmineServers()
    {
        return count($this->config);
    }

    /**
     * Control if a confifuration exists for the redmine identifier provided
     *
     * @param string $redmineId redmine identifier
     *
     * @return bool
     */
    public function hasRedmineServer($redmineId)
    {
        return array_key_exists($redmineId, $this->config) ? true : false;
    }

    /**
     * Retrieve the default redmine identifier
     *
     * @return bool|string
     */
    public function getDefaultRedmineId()
    {
        $return = false;
        if (count($this->config) == 1) {
            $return = key($this->config);
        }

        return $return;
    }

    /**
     * Retrieve configuration for redmine identifier provided
     *
     * @param string $redmineId redmine identifier
     *
     * @return bool|string
     */
    public function getRedmineConfig($redmineId)
    {
        $return = false;
        if (array_key_exists($redmineId, $this->config)) {
            $return = $this->config[$redmineId];
        }

        return $return;
    }

    /**
     * Retrieve requested redmine parameter value
     *
     * @param string $redmineId redmine identifier
     * @param string $paramId   redmine param identifier
     *
     * @return bool|string
     */
    public function getRedmineParam($redmineId, $paramId)
    {
        $return = false;
        if (array_key_exists($redmineId, $this->config) &&
            array_key_exists($paramId, $this->config[$redmineId])
        ) {
            $return = $this->config[$redmineId][$paramId];
        }

        return $return;
    }

    /**
     * Retrieve redmines configuration
     *
     * @return string
     */
    public function getRedminesConfig()
    {
        return $this->config;
    }

    /**
     * Save new redmine configuration into configuration file
     *
     * @param array $params redmine server configuration params
     *
     * @return boolean
     */
    public function addRedmineConfig($params)
    {
        $identifier   = $params[0];
        $url          = $params[1];
        $token        = $params[2];
        $name         = implode(' ', array_slice($params, 3));
        $newConfig    = array(
            $identifier => array(
                'name'    => $name,
                'url'     => $url,
                'api-key' => $token
            )
        );
        $this->config = array_merge($this->config, $newConfig);

        return $this->saveSettingsFile();
    }

    /**
     * @param $redmineId
     *
     * @return bool
     */
    public function removeRedmineConfig($redmineId)
    {
        unset($this->config[$redmineId]);

        return $this->saveSettingsFile();
    }

    /**
     * Save configuration into settings file
     *
     * @return bool
     */
    protected function saveSettingsFile()
    {
        $jsonContent = '';
        if (count($this->config)) {
            $jsonPretty  = new JsonPretty();
            $jsonContent = $jsonPretty->prettify($this->config);
        }

        $return = file_put_contents($this->configFile, $jsonContent) === false ? false : true;

        return $return;
    }
}
