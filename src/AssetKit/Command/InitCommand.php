<?php
namespace AssetKit\Command;
use CLIFramework\Command;
use AssetKit\Config;

class InitCommand extends Command
{
    public function brief()
    {
        return 'initialize assetkit config file.';
    }

    public function options($opts)
    {
        $opts->add('baseUrl:','base URL');
        $opts->add('baseDir:','base directory');
    }

    public function execute()
    {
        $publicRoot = $this->options->public ?: 'public' . DIRECTORY_SEPARATOR . 'assets';
        $this->logger->info( "Using public asset directory: $publicRoot" );

        // create asset config
        $config = new Config('.assetkit.php');

        $config->setBaseUrl($this->options->baseUrl );
        $config->setBaseDir($this->options->baseDir );

        $this->logger->info('Writing config file .assetkit.php');
        $config->save();
    }

}


