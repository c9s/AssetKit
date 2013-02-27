<?php
namespace AssetKit\Command;
use AssetKit\Asset;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
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

        $updater = new \AssetKit\ResourceUpdater();
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



