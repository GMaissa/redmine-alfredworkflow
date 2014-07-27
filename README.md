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
        "clients":{
            "name": "Redmine clients",
            "url": "http://clients.novactive.net",
            "api-key": "62d47be757d978bc7152d52f3a6c570518929ddd"
        },
        "hosting":{
            "name": "Redmine hosting",
            "url": "http://hosting.novactive.net",
            "api-key": "4e53c95016b2b639b8f326a700280a074dbf42ff"
        }
    }';
