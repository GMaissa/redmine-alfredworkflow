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

use Alfred\Utilities as Util;
use Symfony\Component\Filesystem\Filesystem;
use Camspiers\JsonPretty\JsonPretty;

/**
 * Redmine Workflow Storage Abstract class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
abstract class Json
{
    /**
     * Path to Alfred storage directory
     * @var string $alfredDataPath
     */
    protected $alfredDataPath = '';

    /**
     * The data directory for the workflow
     * @type string $dataPath
     */
    private $dataPath = null;

    /**
     * The data filename for the workflow
     * @type string $dataFile
     */
    protected $dataFile = false;

    /**
     * The cached data for the workflow
     * @var array $cacheData
     */
    private $data = array();

    /**
     * The bundle ID for the workflow.
     * @type string $bundle
     */
    private $bundle = null;

    /**
     * The working directory for the workflow.
     * @type string $path
     */
    private $path = null;

    /**
     * The current user's `$HOME` directory.
     * @type string $home
     */
    private $home = null;

    /**
     * Filesystem object
     * @var mixed $fSys
     */
    private $fSys = false;

    /**
     * Getter for data property
     *
     * @return false|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Setter for data property
     *
     * @param array $data data to store
     *
     * @return \AlfredWorkflow\Redmine\Storage\JSon
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Check if we have data
     *
     * @return bool
     */
    public function hasData()
    {
        return $this->getData() === false ? false : true;
    }

    /**
     * Check if we have data for a provided key
     *
     * @param string $key data key to check existence
     *
     * @return bool
     */
    public function hasDataForKey($key)
    {
        return ($this->hasData() && array_key_exists($key, $this->getData())) ? true : false;
    }

    /**
     * Retrieve data for provided key
     *
     * @param string $key  data key to retrieve
     *
     * @return mixed
     */
    public function getDataForKey($key)
    {
        $return = null;
        if ($this->hasDataForKey($key)) {
            $data = $this->getData();
            $return = $data[$key];
        }

        return $return;
    }

    /**
     * Cache data for a given key
     *
     * @param string $key     data key to update
     * @param mixed  $keyData data to store
     */
    public function setDataForKey($key, $keyData)
    {
        $data = $this->getData();
        if ($keyData) {
            $data[$key] = $keyData;
        } else {
            unset($data[$key]);
        }

        $this->setData($data);
    }

    /**
     * Class constructor
     *
     * @param string $bundleId The bundle ID to give to the workflow.
     * @param mixed  $dataPath Override the default storage path for test purpose
     */
    public function __construct($bundleId, $dataPath = false)
    {
        defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

        $this->fSys   = new Filesystem;
        $this->path   = Util::run('pwd');
        $this->home   = Util::run('printf $HOME');
        $this->bundle = $bundleId;

        if ($dataPath) {
            $this->dataPath = $dataPath;
        } else {
            // @codeCoverageIgnoreStart
            $this->dataPath = $this->home . $this->alfredDataPath . DS . $this->bundle;
        }
        // @codeCoverageIgnoreEnd
        if (substr($this->dataPath, -1) != DS) {
            $this->dataPath .= DS;
        }

        if (!$this->fSys->exists($this->dataPath)) {
            $this->fSys->mkdir($this->dataPath);
        }
    }

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
            file_get_contents($this->dataPath . $this->dataFile) != ''
        ) {
            $tmpData = json_decode(file_get_contents($this->dataPath . $this->dataFile), true);
            if (is_array($tmpData)) {
                $this->data = $tmpData;
            }
        }
    }

    /**
     * Store data in the file
     *
     * @return bool
     */
    public function save()
    {
        $fileContent = '';
        if (count($this->getData())) {
            $jsonPretty  = new JsonPretty();
            $fileContent = $jsonPretty->prettify($this->getData());
        }

        return file_put_contents($this->dataPath . $this->dataFile, $fileContent) === false ? false : true;
    }
}
