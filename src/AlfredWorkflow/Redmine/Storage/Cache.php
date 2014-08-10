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
    protected $dataFile = 'cache-projects.json';

    /**
     * Cache validity duration
     * @var integer $cacheDuration
     */
    protected $dataDuration = 86400;


    /**
     * Load file data
     *
     * @throws \AlfredWorkflow\Redmine\Storage\Exception if a data filename is not defined
     */
    public function load()
    {
        if (!$this->dataFile) {
            throw new Exception('You need to define a data filename');
        }

        if ($this->fSys->exists($this->dataPath . $this->dataFile) &&
            file_get_contents($this->dataPath . $this->dataFile) != '' &&
            filemtime($this->dataPath . $this->dataFile) > time() - $this->dataDuration
        ) {
            $tmpData = json_decode(file_get_contents($this->dataPath . $this->dataFile), true);
            if (is_array($tmpData)) {
                $this->data = $tmpData;
            }
        }
    }
}
