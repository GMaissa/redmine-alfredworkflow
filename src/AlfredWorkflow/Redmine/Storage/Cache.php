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
 * Redmine Workflow Cache management class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class Cache extends Json
{
    /**
     * Path to Alfred storage directory
     * @var string $alfredDataPath
     */
    protected $alfredDataPath = '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data';

    /**
     * Path to the workflow data file
     * @var string $dataFile
     */
    protected $dataFile = 'projects.json';

    /**
     * Cache validity duration
     * @var integer $cacheDuration
     */
    protected static $dataDuration = 86400;

    /**
     * Load file data
     */
    public function load()
    {
        if ($this->fSys->exists($this->dataPath . $this->dataFile) &&
            file_get_contents($this->dataPath . $this->dataFile) != '' &&
            filemtime($this->dataPath . $this->dataFile) > time() - self::$dataDuration
        ) {
            $tmpData = json_decode(file_get_contents($this->dataPath . $this->dataFile), true);
            if (is_array($tmpData)) {
                $this->data = $tmpData;
            }
        }
    }

    /**
     * Setter for data duration
     *
     * @param integer $duration data duration value
     */
    public static function setDataDuration($duration)
    {
        if (is_int($duration)) {
            self::$dataDuration = $duration;
        }
    }
}
