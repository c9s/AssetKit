<?php
namespace AssetKit\Command;
use CLIFramework\Command;

class InitCommand extends Command
{
    function options($opts)
    {
        $opts->add('p|public:','public static root');
    }

    function execute()
    {
        $publicRoot = $this->options->public ?: 'public';

        // write config file
        $json = json_encode(array(
            'public' => $publicRoot,
            'assets' => array(),
        ));

        $this->logger->info('Writing config file .assetkit');
        file_put_contents('.assetkit', $json );
    }

}


