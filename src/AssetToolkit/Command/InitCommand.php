<?php
namespace AssetToolkit\Command;
use CLIFramework\Command;
use AssetToolkit\AssetConfig;

class InitCommand extends Command
{
    public function brief()
    {
        return 'initialize assetkit config file.';
    }

    public function options($opts)
    {
        // required options
        $opts->add('baseUrl:','base URL');
        $opts->add('baseDir:','base directory');

        $opts->add('assetdir+','asset directory for looking up assets.');

        // optinoal option
        $opts->add('config?','config file');
    }


    public function execute()
    {
        $configFile = $this->options->config ?: ".assetkit.php";

        if(! $this->options->baseUrl) {
            return $this->logger->error("--baseUrl option is required.");
        }

        if(! $this->options->baseDir) {
            return $this->logger->error("--baseDir option is required.");
        }

        $this->logger->info( "Reading $configFile file" );

        // create asset config
        $config = new AssetConfig($configFile);
        $config->setBaseUrl($this->options->baseUrl );
        $config->setBaseDir($this->options->baseDir );

        $compiledDir = $config->getCompiledDir();
        $this->logger->info("Creating compiled dir: $compiledDir");
        $this->logger->info("Please chmod this directory as you need.");
        if( ! file_exists($compiledDir) )
            mkdir($compiledDir,0755,true);

        if($this->options->assetdir) {
            foreach($this->options->assetdir as $dir) {
                $this->logger->info("Adding asset directory $dir");
                $config->addAssetDirectory($dir);
            }
        }

        $this->logger->info("Writing config file $configFile");
        $config->save();
    }

}


