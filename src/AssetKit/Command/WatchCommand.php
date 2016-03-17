<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\Asset;
use CLIFramework\Command;
use RuntimeException;
use AssetKit\CompilerRunner\CoffeeRunner;
use AssetKit\CompilerRunner\ScssRunner;
use AssetKit\CompilerRunner\SassRunner;
use Exception;

class WatchCommand extends BaseCommand
{
    public function brief()
    {
        return 'Watch an asset.';
    }

    public function execute($assetName)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $assetNames = func_get_args();

        $this->logger->info("Loading assets " . join(', ', $assetNames));

        $assets = array();
        foreach($assetNames as $assetName) {
            if ($a = $loader->load($assetName)) {
                $this->logger->info("-> Asset $assetName loaded");
                $assets[] = $a;
            } else {
                throw new RuntimeException("Unable to load asset $assetName");
            }
        }

        $processCnt = 0;

        // Find asset that defines {'compile'} option or {'compiler','source'} option
        foreach($assets as $asset) {
            $collections = $asset->getCollections();
            foreach($collections as $collection) {
                $command = NULL;
                if (isset($collection['compile'])) {
                    $command = $collection['compile'];
                } elseif (isset($collection['compiler']) && isset($collection['source'])) {
                    switch($collection['compiler']) {
                    case "sass":
                        $runner = new SassRunner;
                        $runner->enableCompass();
                        break;
                    case "scss":
                        $runner = new ScssRunner;
                        $runner->enableCompass();
                        break;
                    case "coffee":
                        $runner = new CoffeeRunner;
                        break;
                    default:
                        throw new Exception("Unsupported compiler type: " . $collection['compiler']);
                    }
                    foreach($collection['source'] as $source) {
                        $runner->addSourceArgument($source);
                    }
                    $cmd = $runner->buildWatchCommand();
                    $command = join(' ', $cmd);
                } else if (isset($collection['watch'])) {
                    $command = $collection['watch'];
                }
                if (!$command) {
                    continue;
                }

                $this->logger->debug("Found command: $command");
                $this->logger->debug("Forking process #{$processCnt} to watch collection files...");
                $processCnt++;
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception("Can't fork process");
                } elseif ($pid == 0) {
                    $this->logger->debug("Changing directory to: " . $asset->getSourceDir());
                    chdir($asset->getSourceDir());

                    $this->logger->debug("Executing command: " . $command);
                    system($command, $retval);
                    exit($retval);
                }
            }
        }

        // Waiting process
        pcntl_wait($status); // Protect against Zombie children
        $this->logger->debug("$processCnt stopped.");
    }
}


