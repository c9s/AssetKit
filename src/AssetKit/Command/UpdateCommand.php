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

class UpdateCommand extends Command
{
    public function brief() { return 'update and install assets'; }

    public function options($opts)
    {
        $opts->add('l|link','link asset files, instead of copy install.');
        $opts->add('config?','config file');
    }

    public function execute()
    {
        $configFile = $this->options->config ?: ".assetkit.php";

        $options = $this->options;

        $config = new AssetConfig('.assetkit');
        $loader = new AssetLoader($config);

        $installer = $options->link
                ? new LinkInstaller
                : new Installer;
        $installer->logger = $this->logger;

        $loader->updateAssetManifests();

        foreach( $config->getRegisteredAssets() as $name => $config ) {
            $this->logger->info("Updating $name ...");

            $updater = new \AssetKit\ResourceUpdater;
            $updater->update($asset, true);

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );
        }
        $this->logger->info("Done");
    }
}


