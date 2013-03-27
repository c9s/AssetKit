<?php
namespace AssetToolkit;
use CLIFramework\Application;

class Console extends Application
{
    const NAME = 'assetkit';
    const VERSION = "2.1.8";

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
        $this->registerCommand('clean');
        $this->registerCommand('update');
        $this->registerCommand('install');
        $this->registerCommand('target');
        $this->registerCommand('list');
        $this->registerCommand('set');
    }
}

