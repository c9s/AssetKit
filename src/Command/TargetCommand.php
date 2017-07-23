<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use AssetKit\Command\BaseCommand;
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

    public function init()
    {
        $this->command('list', ListTargetCommand::class);
        $this->command('add', AddTargetCommand::class);
        $this->command('remove', RemoveTargetCommand::class);
    }

    public function execute()
    {
        $list = $this->createCommand(ListTargetCommand::class);
        $list->execute();
    }
}


