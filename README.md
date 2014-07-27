Alfred Workflow for Redmine
===========================
An [Alfred](http://alfredapp.com) workflow that speed up access to Redmine projet pages

[![Build Status](https://travis-ci.org/GMaissa/redmine-alfredworkflow.svg)](https://travis-ci.org/GMaissa/redmine-alfredworkflow)

### Requirements
* [Alfred v2](http://alfredapp.com)
* [Alfred Powerpack](http://www.alfredapp.com/powerpack)
* Enable REST API from the "/settings?tab=authentication" page on your Redmine server
* Generate your API Key from the "/my/account" page on your Redmine server

### Installation
[![Get Redmine workflow](https://raw.github.com/hzlzh/AlfredWorkflow.com/master/index/css/images/downlaod-btn.png)](https://github.com/GMaissa/redmine-alfredworkflow/raw/master/Redmine.alfredworkflow)

Once installed, in Alfred, edit the workflow's "Script Filter" to add your Redmine server configuration :

    // Complete your Redmine configuration bellow
    $config='
    {
        "test1":{
            "name": "Redmine test1",
            "url": "http://test1.com",
            "api-key": ""
        },
        "test2":{
            "name": "Redmine Test 2",
            "url": "http://test2.com",
            "api-key": "api-key-test2"
        }
    }';
