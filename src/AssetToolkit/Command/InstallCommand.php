<?php
namespace AssetToolkit\Command;
use AssetToolkit\Asset;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\FileUtils;
use AssetToolkit\Installer;
use AssetToolkit\LinkInstaller;
use CLIFramework\Command;
use Exception;

class InstallCommand extends Command
{

    public function brief() 
    {
        return 'install assets';
    }

    public function options($opts)
    {
        $opts->add('l|link','link asset files, instead of copy install.');
        $opts->add('config?','config file');
    }

    public function execute()
    {
        $options = $this->options;
        $configFile = $this->options->config ?: ".assetkit.php";

        $installer = $options->link
                ? new LinkInstaller
                : new Installer;

        $installer->logger = $this->logger;


        $config = new AssetConfig($configFile);
        $loader = new AssetLoader($config);
        $loader->updateAssetManifests();

        $updater = new \AssetToolkit\ResourceUpdater();
        foreach( $config->getRegisteredAssets() as $name => $config ) {
            $asset = $loader->load($name);

            $this->logger->info("Updating $name ...");
            $updater->update($asset);

            $this->logger->info( "Installing {$asset->name}" );
            $installer->install( $asset );
        }
        $config->save();
        $this->logger->info("Done");
    }
}


