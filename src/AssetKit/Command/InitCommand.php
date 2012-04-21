<?php
namespace AssetKit\Command;
use CLIFramework\Command;
use AssetKit\Config;

class InitCommand extends Command
{
    function options($opts)
    {
        $opts->add('p|public:','public static root');
    }

    function execute()
    {
        $publicRoot = $this->options->public ?: 'public';

        $config = new Config( '.assetkit' );
        $config->config = array(
            'public' => $publicRoot,
            'assets' => array(),
        );

        $this->logger->info('Writing config file .assetkit');
        $config->save();
    }

}


