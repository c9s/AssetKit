<?php
namespace AssetToolkit\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\Asset;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use AssetToolkit\Command\BaseCommand;
use Exception;

class TargetCommand extends BaseCommand
{
    public function brief() { return 'add, remove, list asset targets'; }

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('remove:', 'remove target');
        $opts->add('add:', 'add target');
    }

    public function init() {
        $this->registerCommand('list', 'AssetToolkit\Command\ListTargetCommand');
        $this->registerCommand('add', 'AssetToolkit\Command\AddTargetCommand');
        $this->registerCommand('remove', 'AssetToolkit\Command\RemoveTargetCommand');
    }

    public function execute()
    {
        $list = $this->createCommand('AssetToolkit\Command\ListTargetCommand');
        $list->execute();
    }
}


