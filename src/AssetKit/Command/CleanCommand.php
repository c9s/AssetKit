<?php
namespace AssetKit\Command;
use Exception;
use DateTime;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetCompiler;
use AssetKit\Asset;
use AssetKit\Cache;
use AssetKit\CacheFactory;
use AssetKit\Command\BaseCommand;
use AssetKit\ProductionAssetCompiler;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;

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

        $this->logger->info("Cleaning up caches...");
        $cache = CacheFactory::create($config);
        $cache->clear();

        $compiler = new ProductionAssetCompiler($config, $loader);

        $compiledDir = $config->getCompiledDir();

        foreach( $loader->entries->getTargets() as $targetId => $assetNames) {
            $metaFile = $compiledDir . DIRECTORY_SEPARATOR . $compiler->buildTargetMetaFilename($targetId);
            if (file_exists($metaFile)) {
                $entries = require $metaFile;
                foreach($entries as $entry) {
                    foreach(array('js_file', 'css_file') as $key) {
                        if (isset($entry[$key])) {
                            $file = $entry[$key];
                            file_exists($file) && unlink($file);
                        }
                    }
                }
                unlink($metaFile);
            }
        }

        foreach( $loader->entries as $entry ) {
            $asset = new Asset;
            $asset->loadFromManifestFile($entry['manifest']);
            $metaFile = $compiledDir . DIRECTORY_SEPARATOR . $compiler->buildAssetMetaFilename($asset);
            if (file_exists($metaFile)) {
                $entries = require $metaFile;
                foreach(array('js_file', 'css_file') as $key) {
                    if (isset($entries[$key])) {
                        $file = $entries[$key];
                        file_exists($file) && unlink($file);
                    }
                }
                unlink($metaFile);
            }
        }
    }
}




