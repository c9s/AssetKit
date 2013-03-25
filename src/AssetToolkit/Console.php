<?php
namespace AssetToolkit;
use CLIFramework\Application;

class Console extends Application
{
    const name = 'assetkit';
    const version = '2.0.0';

    static function getInstance()
    {
        static $self;
        $self = new self;
        return $self;
    }

    public function init()
    {
        parent::init();
        $this->registerCommand('init');
        $this->registerCommand('add');
        $this->registerCommand('remove');
        $this->registerCommand('compile');
        $this->registerCommand('update');
        $this->registerCommand('install');
        $this->registerCommand('target');
    }
}

