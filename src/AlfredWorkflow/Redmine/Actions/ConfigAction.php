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

use Redmine\Client;

/**
 * Redmine Workflow Configuration class
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */
class ConfigAction extends BaseAction
{
    /**
     * List of available actions, and their configuration
     * @var array $actions
     */
    protected $actions = array(
        'add' => array(
            'method' => 'addRedmine',
            'name'   => 'Add new Redmine server config',
            'icon'   => 'assets/icons/add.png'
        ),
        'remove'  => array(
            'method' => 'removeRedmine',
            'name'   => 'Remove existing Redmine server config',
            'icon'   => 'assets/icons/remove.png'
        )
    );

    /**
     * Run the workflow
     *
     * @param string $query Alfred query string
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
                if (preg_match('/' . $actionPattern . '/', $identifier)) {
                    $result = array(
                        'uid'          => $identifier,
                        'arg'          => '',
                        'title'        => $identifier,
                        'subtitle'     => $config['name'],
                        'icon'         => $config['icon'],
                        'valid'        => 'no',
                        'autocomplete' => sprintf('%s ', $identifier)
                    );
                    $this->workflow->result($result);
                }
            }
        }
    }

    /**
     * Add a redmine server configuration
     *
     * @param array $params action parameters
     */
    protected function addRedmine($params)
    {
        $subtitle = false;
        $config   = $this->actions['add'];

        try {
            $this->testAddParams($params);
        } catch (Exception $e) {
            $subtitle = $e->getMessage();
        }
        if (count($params) >= 4 && !$subtitle) {
            $this->workflow->result(
                array(
                    'uid'      => '',
                    'arg'      => 'add ' . implode(' ', $params),
                    'title'    => $config['name'],
                    'subtitle' => '',
                    'icon'     => $config['icon'],
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
                    'title'        => $config['name'],
                    'subtitle'     => $subtitle,
                    'icon'         => $config['icon'],
                    'valid'        => 'no',
                    'autocomplete' => 'add ' . $additionalAutoComp
                )
            );
        }
    }

    /**
     * Test add action parameters
     *
     * @param array $params action parameters
     *
     * @throws \AlfredWorkflow\Redmine\Actions\Exception is on of the parameters is invalid
     */
    protected function testAddParams($params)
    {
        if (isset($params[0]) && $this->settings->hasDataForKey($params[0])) {
            throw new Exception('Identifier ' . $params[0] . ' already exists');
        }
        if (isset($params[1]) && !filter_var($params[1], FILTER_VALIDATE_URL)) {
            throw new Exception('Redmine URL ' . $params[1] . ' not valid');
        }
        if (isset($params[3])) {
            if (!isset($this->redmineClient[$params[0]])) {
                // @codeCoverageIgnoreStart
                $this->redmineClient[$params[0]] = new Client($params[1], $params[2]);
            }
            // @codeCoverageIgnoreEnd
            try {
                $response = $this->redmineClient[$params[0]]->api('user')->getCurrentUser();
                if (!$response) {
                    throw new Exception(
                        'Impossible to connect to the Redmine server with the URL and api-key provided'
                    );
                }
            } catch (\Exception $e) {
                throw new Exception('Impossible to connect to the Redmine server with the URL and api-key provided');
            }
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
        $actionConfig   = $this->actions['remove'];

        if ($this->settings->hasDataForKey($redminePattern)) {
            $result = array(
                'uid'      => '',
                'arg'      => 'remove ' . $redminePattern,
                'title'    => $redminePattern,
                'subtitle' => sprintf(
                    'Remove %s configuration',
                    $this->settings->getRedmineParam($redminePattern, 'name')
                ),
                'icon'     => $actionConfig['icon'],
                'valid'    => 'yes'
            );
            $this->workflow->result($result);
        } else {
            foreach ($this->settings->getData() as $identifier => $config) {
                $result = array(
                    'uid'      => '',
                    'arg'      => 'remove ' . $identifier,
                    'title'    => $identifier,
                    'subtitle' => sprintf('Remove %s configuration', $config['name']),
                    'icon'     => $actionConfig['icon'],
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
     *                      "remove <identifier>"
     *
     * @return string
     */
    public function save($query)
    {
        $return = false;
        $params = explode(' ', $query);
        $action = $params[0];
        array_shift($params);

        if ('add' == $action && $this->settings->addRedmineConfig($params)) {
            $return = 'Configuration added';
        } elseif ('remove' == $action && $this->settings->removeRedmineConfig($params[0])) {
            $return = 'Configuration removed';
        }

        return $return;
    }
}
