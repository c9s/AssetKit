<?php
namespace AssetKit\Command;
use Exception;
use DateTime;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetCompiler;
use AssetKit\Asset;
use AssetKit\Cache;
use AssetKit\Command\BaseCommand;
use CLIFramework\Command;

/**
 * Command to clean up cache
 */
class CleanCommand extends BaseCommand
{
    public function options($opts)
    {
        parent::options($opts);
    }

    public function brief() { return 'Clean up caches'; }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $cache = Cache::create($config);
        $cache->clear();
    }
}




