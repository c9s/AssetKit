<?php
namespace AssetKit\Command;
use AssetKit\Config;
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

    public function execute($manifestPath)
    {
        $options = $this->options;

        $configFile = $this->options->config ?: ".assetkit.php";
        $config = new \AssetKit\Config($configFile);

        if( is_dir($manifestPath) ) {
            $manifestPath = $manifestPath  . DIRECTORY_SEPARATOR . 'manifest.yml';
        }

        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        /*
        $asset = new Asset($manifestPath);
        $asset->config = $config;

        $this->logger->info("Initializing resource...");
        $asset->initResource(true); // update it

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
        */
    }
}


