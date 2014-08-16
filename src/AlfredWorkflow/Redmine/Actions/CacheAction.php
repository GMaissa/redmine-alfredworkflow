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

use Monolog\Logger;

/**
 * Redmine Workflow Configuration class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class CacheAction extends BaseAction
{
    /**
     * List of available actions, and their configuration
     * @var array $actions
     */
    protected $actions = array(
        'clear' => array(
            'uid'      => 'clear',
            'arg'      => 'clear',
            'title'    => 'clear',
            'subtitle' => 'Clear workflow cache data',
            'icon'     => 'assets/icons/clear-cache.png',
            'valid'    => 'yes'
        )
    );

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
     */
    public function run($query)
    {
        // Need this because of PHPMD error
        $query;

        foreach ($this->actions as $params) {
            $this->workflow->result($params);
        }
    }

    /**
     * Update and save settings file
     *
     * @param string $query parameters for save actions, can be
     *                      "add <identifier> <url> <api-key> <name>"
     *                      "rm <identifier>"
     *
     * @throws \AlfredWorkflow\Redmine\Actions\Exception if the provided action does not exists
     * @return string
     */
    public function save($query)
    {
        $params = explode(' ', $query);
        $action = $params[0];
        $return = false;

        if ('clear' == $action && $this->cache->setData(array())->save()) {
            $return = 'Cache cleared';
        } else {
            $this->throwException(sprintf('Cache action %s does not exists.', $action), __METHOD__, Logger::ERROR);
        }

        return $return;
    }
}
