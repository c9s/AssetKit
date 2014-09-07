<?php
namespace AssetKit\Command;
use CLIFramework\Command;
use AssetKit\AssetConfig;
use AssetKit\Command\BaseCommand;

use ConfigKit\ConfigCompiler;

// $config = ConfigCompiler::load('tests/ConfigKit/data/framework.yml');

class InitCommand extends BaseCommand
{
    public function brief()
    {
        return 'initialize assetkit config file.';
    }

    public function options($opts)
    {
        parent::options($opts);

        // required options
        $opts->add('baseUrl:','base URL')
            ->required()
            ;
        $opts->add('baseDir:','base directory')
            ->isa('path')
            ->required()
            ;
        $opts->add('dir+','asset directory for looking up assets.')
            ->isa('path')
            ->required()
            ;
    }

    public function arguments($args) {
        $args->add('configFile')
            ->isa('file')
            ;
    }

    public function execute($configFile)
    {
        if (! $this->options->baseUrl) {
            return $this->logger->error("--baseUrl option is required.");
        }

        if (! $this->options->baseDir) {
            return $this->logger->error("--baseDir option is required.");
        }

        // create asset config
        $config = $this->getAssetConfig();
        $config->setBaseUrl($this->options->baseUrl );
        $config->setBaseDir($this->options->baseDir );
        if ($dirs = (array) $this->options->dir) {
            foreach($dirs as $dir) {
                $this->logger->info("Adding asset directory $dir");
                $config->addAssetDirectory($dir);
            }
        }
        $this->logger->info("Saving config to $configFile");
        $config->save();


        if (file_exists($this->getAssetConfigLink()) ) {
            unlink($this->getAssetConfigLink());
        }
        $this->logger->info("Creating link {$this->getAssetConfigLink()} for $configFile");
        symlink($configFile, $this->getAssetConfigLink());

        $compiledDir = $config->getCompiledDir();
        if ( ! file_exists($compiledDir) ) {
            $this->logger->info("Creating compiled dir: $compiledDir");
            mkdir($compiledDir,0777,true);
        } else {
            $this->logger->info("Changing directory permission to 777 for enabling compilation through nginx/apache.");
            $this->logger->info("(You will need write permission to be opened for AssetCompiler.)");
            chmod($compiledDir, 0777);
        }
        $this->logger->info("Done");
    }

}


