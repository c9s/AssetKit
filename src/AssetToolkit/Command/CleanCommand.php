<?php
namespace AssetToolkit\Command;
use Exception;
use DateTime;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetCompiler;
use AssetToolkit\Asset;
use AssetToolkit\Cache;
use AssetToolkit\Command\BaseCommand;
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

    public function brief() { return 'precompile asset files.'; }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $cache = Cache::create($config);
        $cache->clear();
    }
}




