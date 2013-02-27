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

        if( is_dir($manifestFile) ) {
            $manifestFile = $manifestFile  . DIRECTORY_SEPARATOR . 'manifest.yml';
        }

        if( ! file_exists($manifestFile)) 
            throw new Exception( "$manifestFile does not exist." );

        $loader = new AssetLoader($config);
        $asset = $loader->loadFromManifestFile($manifestFile);

        $this->logger->info("Initializing resource...");

        $updater = new \AssetKit\ResourceUpdater($this);
        $updater->update(true);

        $this->logger->info( "Installing {$asset->name}" );

        if( $options->link ) {
            $installer = new LinkInstaller;
            $installer->install( $asset );
        } 
        else {
            $installer = new Installer;
            $installer->install( $asset );
        }

        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();

        $this->logger->info("Done");
    }
}


