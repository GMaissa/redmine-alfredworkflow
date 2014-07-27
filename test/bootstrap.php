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

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) &&
    (!$loader = includeIfExists(__DIR__.'/../../../.composer/autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:'."\n".
        'curl -s http://getcomposer.org/installer | php'."\n".
        'php composer.phar install'."\n");
}

$loader->add('Redmine\Tests', __DIR__);

return $loader;
