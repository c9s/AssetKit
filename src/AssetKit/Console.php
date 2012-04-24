<?php
namespace AssetKit;
use CLIFramework\Application;

class Console extends Application
{
    const name = 'assetkit';
    const version = '0.0.1';

    static function getInstance()
    {
        static $self;
        $self = new self;
        return $self;
    }

    function init()
    {
        parent::init();
        $this->registerCommand('init');
        $this->registerCommand('add');
        $this->registerCommand('remove');
        $this->registerCommand('precompile');
    }
}

