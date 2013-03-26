<?php
namespace AssetToolkit\Command;
use CLIFramework\Command;
use AssetToolkit\AssetConfig;
use AssetToolkit\Command\BaseCommand;

class SetCommand extends BaseCommand
{
    public function brief() { return 'set config.'; }

    public function options($opts)
    {
        parent::options($opts);
    }

    public function execute($name,$value)
    {
        // create asset config
        $config = $this->getAssetConfig();

        switch($name) {
        case "baseurl":
            $config->setBaseUrl($value);
            break;
        case "basedir":
            $config->setBaseDir($value);
            break;
        case "namespace":
            $config->setNamespace($value);
            break;
        case "cachedir":
            $config->setCacheDir($value);
            break;
        default:
            $this->logger->error("Unsupported option: $name");
            break;
        }
        $config->save();
    }

}


