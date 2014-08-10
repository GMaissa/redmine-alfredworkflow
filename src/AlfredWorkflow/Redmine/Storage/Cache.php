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
}
