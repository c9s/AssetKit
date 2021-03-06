<?php
namespace AssetKit;
use CLIFramework\Application;

class Console extends Application
{
    const NAME = 'assetkit';
    const VERSION = "3.4.1";

    static function getInstance()
    {
        static $self;
        $self = new self;
        return $self;
    }

    public function init()
    {
        parent::init();
        $this->command('init');
        $this->command('create-manifest');
        $this->command('add');
        $this->command('remove');
        $this->command('compile');
        $this->command('clean');
        $this->command('update');
        $this->command('install');
        $this->command('target');
        $this->command('list');
        $this->command('watch');
    }
}

