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

namespace AlfredWorkflow\Redmine\Storage;

/**
 * Redmine Settings class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class Settings extends Json
{
    /**
     * Path to Alfred storage directory
     * @var string $alfredDataPath
     */
    protected $alfredDataPath = '/Library/Application Support/Alfred 2/Workflow Data';

    /**
     * Workflow configuration file path
     * @var string $dataFile
     */
    protected $dataFile = 'settings.json';

    /**
     * Retrieve number of Redmine servers configured
     *
     * @return integer
     */
    public function nbRedmineServers()
    {
        return count($this->getData());
    }

    /**
     * Retrieve the default redmine identifier
     *
     * @return mixed
     */
    public function getDefaultRedmineId()
    {
        $return = false;
        if (count($this->getData()) == 1) {
            $return = key($this->getData());
        }

        return $return;
    }

    /**
     * Retrieve requested redmine parameter value
     *
     * @param string $redmineId redmine identifier
     * @param string $paramId   redmine param identifier
     *
     * @return mixed
     */
    public function getRedmineParam($redmineId, $paramId)
    {
        $return = false;
        $data = $this->getData();
        if (isset($data[$redmineId]) &&
            isset($data[$redmineId][$paramId])
        ) {
            $return = $data[$redmineId][$paramId];
        }

        return $return;
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
            'name'    => $name,
            'url'     => $url,
            'api-key' => $token
        );
        $this->setDataForKey($identifier, $newConfig);

        return $this->save();
    }

    /**
     * @param $redmineId
     *
     * @return bool
     */
    public function removeRedmineConfig($redmineId)
    {
        $this->setDataForKey($redmineId, false);

        return $this->save();
    }
}
