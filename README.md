Alfred Workflow for Redmine
===========================
An [Alfred](http://alfredapp.com) workflow that speeds up access to Redmine project pages

[![Build Status](https://travis-ci.org/GMaissa/redmine-alfredworkflow.svg)](https://travis-ci.org/GMaissa/redmine-alfredworkflow)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7de26d83-26e8-475b-9eaa-dcab53df8ec0/small.png)](https://insight.sensiolabs.com/projects/7de26d83-26e8-475b-9eaa-dcab53df8ec0)

### Requirements
* [Alfred v2](http://alfredapp.com)
* [Alfred Powerpack](http://www.alfredapp.com/powerpack)
* Enable the REST API, from the "/settings?tab=authentication" page on your Redmine server
* Generate your API Key, from the "/my/account" page on your Redmine server

### Installation
[![Get Redmine workflow](https://raw.github.com/hzlzh/AlfredWorkflow.com/master/index/css/images/downlaod-btn.png)](https://github.com/GMaissa/redmine-alfredworkflow/raw/master/Redmine.alfredworkflow)

Once installed, use the red-config key to setup your redmine server configuration. You can use the command :

    red-config add <redmine-server-identifier> <redmine-server-url> <api-key> <redmine-server-name>

Example :

    red-config add myredmine http://redmine.test.com my-personal-api-key My Redmine server
