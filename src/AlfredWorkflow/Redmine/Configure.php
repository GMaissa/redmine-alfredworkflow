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

use Alfred\Workflow;

/**
 * Redmine Workflow Configuration class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class Configure
{
    /**
     * Workflow settings
     * @var string $settings
     */
    protected $settings = false;

    /**
     * Workflow object to format data for alfred
     * @var \Alfred\Workflow $_workflow
     */
    protected $workflow;

    protected $actions = array(
        'add' => array(
            'method' => 'addRedmine',
            'name'   => 'Add new redmine configuration'
        ),
        'rm'  => array(
            'method' => 'removeRedmine',
            'name'   => 'Remove existing redmine configuration'
        )
    );

    /**
     * Class constructor
     *
     * @param \AlfredWorkflow\Redmine\Settings $settings Settings object
     * @param \Alfred\Workflow                 $workflow Alfred Workflow Api object
     */
    public function __construct(Settings $settings, Workflow $workflow)
    {
        $this->settings = $settings;
        $this->workflow = $workflow;
    }

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
     *
     * @return string
     */
    public function run($query)
    {
        $args          = explode(' ', trim($query));
        $actionPattern = array_key_exists(0, $args) ? $args[0] : null;

        if (array_key_exists($actionPattern, $this->actions)) {
            array_shift($args);
            call_user_func(
                array(
                    $this,
                    $this->actions[$actionPattern]['method']
                ),
                $args
            );
        } else {
            foreach ($this->actions as $identifier => $config) {
                $result = array(
                    'uid'          => $identifier,
                    'arg'          => '',
                    'title'        => $config['name'],
                    'subtitle'     => '',
                    'icon'         => 'icon.png',
                    'valid'        => 'no',
                    'autocomplete' => sprintf('%s ', $identifier)
                );
                $this->workflow->result($result);
            }
        }

        return $this->workflow->toXML();
    }

    /**
     * Add a redmine server configuration
     *
     * @param array $params action parameters
     */
    protected function addRedmine($params)
    {
        $subtitle = false;
        if (array_key_exists(0, $params) && $this->settings->hasRedmineServer($params[0])) {
            $subtitle = 'Identifier ' . $params[0] . ' already exists';
        }
        if (array_key_exists(1, $params) && !filter_var($params[1], FILTER_VALIDATE_URL)) {
            $subtitle = 'Redmine URL ' . $params[1] . ' not valid';
        }
        if (count($params) >= 4 && !$subtitle) {
            $this->workflow->result(
                array(
                    'uid'      => '',
                    'arg'      => 'add ' . implode(' ', $params),
                    'title'    => 'Add Redmine server config',
                    'subtitle' => '',
                    'icon'     => 'icon.png',
                    'valid'    => 'yes'
                )
            );
        } else {
            if (!$subtitle) {
                $subtitle = 'Provide params: <identifier> <url> <api-key> <name>';
            }
            $additionalAutoComp = count($params) ? implode(' ', $params) . ' ' : null;
            $this->workflow->result(
                array(
                    'uid'          => '',
                    'arg'          => '',
                    'title'        => 'Add Redmine server config',
                    'subtitle'     => $subtitle,
                    'icon'         => 'icon.png',
                    'valid'        => 'no',
                    'autocomplete' => 'add ' . $additionalAutoComp
                )
            );
        }
    }

    /**
     * Remove a redmine server configuration from settings file
     *
     * @param array $params action parameters
     */
    protected function removeRedmine($params)
    {
        $redminePattern = array_key_exists(0, $params) ? $params[0] : null;

        if ($this->settings->hasRedmineServer($redminePattern)) {
            $result = array(
                'uid'      => '',
                'arg'      => 'rm ' . $redminePattern,
                'title'    => sprintf(
                    'Remove %s configuration',
                    $this->settings->getRedmineParam($redminePattern, 'name')
                ),
                'subtitle' => '',
                'icon'     => 'icon.png',
                'valid'    => 'yes'
            );
            $this->workflow->result($result);
        } else {
            foreach ($this->settings->getRedminesConfig() as $identifier => $config) {
                $result = array(
                    'uid'      => '',
                    'arg'      => 'rm ' . $identifier,
                    'title'    => sprintf('Remove %s configuration', $config['name']),
                    'subtitle' => '',
                    'icon'     => 'icon.png',
                    'valid'    => 'yes'
                );
                $this->workflow->result($result);
            }
        }
    }

    /**
     * Update and save settings file
     *
     * @param string $query parameters for save actions, can be
     *                      "add <identifier> <url> <api-key> <name>"
     *                      "rm <identifier>"
     *
     * @return string
     */
    public function save($query)
    {
        $params = explode(' ', $query);
        $action = $params[0];
        array_shift($params);

        if ('add' == $action && $this->settings->addRedmineConfig($params)) {
            $return = 'Configuration added';
        } elseif ('rm' == $action && $this->settings->removeRedmineConfig($params[0])) {
            $return = 'Configuration removed';
        }

        return $return;
    }
}
