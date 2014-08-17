<?php
/**
 * Alfred Workflow Redmine
 *
 * Open a Redmine project page
 *
 * @category  AlfredWorkflow
 * @package   AlfredWorkflow.Redmine.Tests
 * @author    Guillaume Maïssa <guillaume@maissa.fr>
 * @copyright 2014 Guillaume Maïssa
 */

require_once( './vendor/autoload.php' );

if (isset($debug)) {
    AlfredWorkflow\Redmine::setDebug($debug);
}

$alfredWorkflow   = new Alfred\Workflow();
$workflowSettings = new AlfredWorkflow\Redmine\Storage\Settings("com.gmaissa.redmine-workflow");
$workflowCache    = new AlfredWorkflow\Redmine\Storage\Cache("com.gmaissa.redmine-workflow");
$redmineWorkflow  = new AlfredWorkflow\Redmine($workflowSettings, $alfredWorkflow, $workflowCache);

echo $redmineWorkflow->$method($actionGroup, $query);
