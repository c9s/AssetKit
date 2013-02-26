<?php
namespace AssetKit;
use CLIFramework\Application;

class Console extends Application
{
    const name = 'assetkit';
    const version = '1.0.0';

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
    }
}

