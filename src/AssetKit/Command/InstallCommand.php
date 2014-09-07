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

class InstallCommand extends BaseCommand
{

    public function brief()
    {
        return 'install assets';
    }

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('l|link','link asset files, instead of copy install.');
    }

    public function execute()
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $loader->updateAssetManifests();

        $installer = $this->getInstaller();
        $installer->logger = $this->logger;


        $compiledDir = $config->getCompiledDir();
        $this->logger->info("Creating compiled dir: $compiledDir");
        $this->logger->info("Please chmod this directory as you need.");
        if ( ! file_exists($compiledDir) )
            mkdir($compiledDir,0755,true);

        $updater = new \AssetKit\ResourceUpdater();
        foreach( $config->all() as $name => $stash ) {
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



