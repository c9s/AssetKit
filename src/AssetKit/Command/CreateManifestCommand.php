<?php
namespace AssetKit\Command;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Asset;
use AssetKit\FileUtils;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use AssetKit\Command\BaseCommand;
use CLIFramework\Command;
use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ConfigKit\ConfigCompiler;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

class CreateManifestCommand extends Command
{
    public function brief() { return 'Create asset manifest file'; }

    public function options($opts) {
        $opts->add('d|dir+', 'Possible relative paths to the manifest file that contains asset files.');
    }


    public function execute($manifestDir = NULL)
    {
        $manifestDir = $manifestDir ?: getcwd();
        $possiblePaths = $this->options->dir ?: array();

        $jsFiles = array();
        $cssFiles = array();
        $imageFiles = array();
        $fontFiles = array();

        $inspectDirs = array(
            'javascript' => array('js','javascript', 'javascripts'),
            'stylesheet' => array('css', 'stylesheet', 'stylesheets'),
            'files' => array('fonts', 'images'),
        );

        $config = array();
        $config['collections'] = array();


        $files = array(
            'stylesheet' => array(),
            'javascript' => array(),
            'file' => array(),
        );
        $patterns = array(
            'stylesheet' => '/\.css$/',
            'javascript' => '/\.js/',
            'file' => '/\.(?:png|jpe?g|ico|gif|eot|svg|ttf|woff)$/',
        );

        if (empty($possiblePaths)) {

            foreach($inspectDirs as $type => $dirs) {
                foreach($dirs as $dir) {
                    if (file_exists($manifestDir . DIRECTORY_SEPARATOR . $dir)) {
                        $this->logger->info("Found $dir as type $type.");
                        $config['collections'][] = array( $type => array($dir) );
                    }
                }
            }

            $di = new \RecursiveDirectoryIterator($manifestDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new \RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach($it as $info) {
                if ($info->isFile()) {
                    $path = $it->getSubPathname();
                    foreach($patterns as $type => $pattern) {
                        if (preg_match($pattern, $path)) {
                            $this->logger->info("Adding $path as $type");
                            $files[$type][] = $path;
                        }
                    }
                }
            }

        } else {
            // Search recursively
            foreach($possiblePaths as $path) {
                $searchPath = $manifestDir . DIRECTORY_SEPARATOR . $path;
                if (!file_exists($searchPath)) {
                    $this->logger->warn("$searchPath does not exist.");
                    continue;
                }

                $di = new \RecursiveDirectoryIterator($searchPath, RecursiveDirectoryIterator::SKIP_DOTS);
                $it = new \RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
                foreach($it as $info) {
                    if ($info->isFile()) {
                        $item = $path . DIRECTORY_SEPARATOR . $it->getSubPathname();
                        foreach($patterns as $type => $pattern) {
                            if (preg_match($pattern, $item)) {
                                $this->logger->info("Adding $item as $type");
                                $files[$type][] = $item;
                            }
                        }
                    }
                }
            }
        }


        // Remove minified files
        $files['javascript'] = array_filter($files['javascript'], function($file) {
            return !preg_match('/[.-](?:min|pack)\.js$/',$file);
        });
        $files['stylesheet'] = array_filter($files['stylesheet'], function($file) {
            return !preg_match('/[.-](?:min|pack)\.css$/',$file);
        });
        foreach($files as $type => $files) {
            if (!empty($files)) {
                $config['collections'][] = array($type => array_values($files));
            }
        }
        ConfigCompiler::write_yaml($manifestDir . DIRECTORY_SEPARATOR . 'manifest.yml', $config);
        $this->logger->info("Done");
    }
}


