<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use CLIFramework\Command;
use Exception;

class AddCommand extends Command
{


    public function brief() { return 'add and initialize asset.'; }

    public function options($opts)
    {
        $opts->add('l|link','link asset files, instead of copy install.');
    }

    public function execute($manifestFile)
    {
        $options = $this->options;

        $configFile = $this->options->config ?: ".assetkit.php";
        $config = new AssetConfig($configFile);

        $loader = new AssetLoader($config);
        $asset = $loader->registerFromManifestFileOrDir($manifestFile);

        if(!$asset) {
            throw new Exception("Can not load asset from $manifestFile.");
        }

        $this->logger->info("Initializing resource...");

        $updater = new \AssetKit\ResourceUpdater();
        $updater->update($asset);

        $this->logger->info( "Installing {$asset->name}" );

        if( $options->link ) {
            $installer = new LinkInstaller;
            $installer->install( $asset );
        } 
        else {
            $installer = new Installer;
            $installer->install( $asset );
        }

        $config->addAsset( $asset );
        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


