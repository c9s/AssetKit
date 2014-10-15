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
use ConfigKit\ConfigCompiler;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

class CreateManifestCommand extends Command
{
    public function brief() { return 'Create asset manifest file'; }

    public function execute($path = NULL)
    {
        if ($path) {
            chdir($path);
        }

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
        foreach($inspectDirs as $type => $dirs) {
            foreach($dirs as $dir) {
                if (file_exists($dir)) {
                    $this->logger->info("Found $dir as type $type.");
                    $config['collections'][] = array( $type => array($dir) );
                    break;
                }
            }
        }

        $jsFiles = glob("*.js");
        $jsFiles = array_filter($jsFiles, function($file) {
            return ! preg_match('/\.min\.js/',$file);
        });
        if (!empty($jsFiles)) {
            $config['collections'][] = array('javascript' => $jsFiles);
        }

        $cssFiles = glob("*.css");
        $cssFiles = array_filter($cssFiles, function($file) {
            return ! preg_match('/\.min\.css$/',$file);
        });

        if (!empty($cssFiles)) {
            $config['collections'][] = array('stylesheet' => $cssFiles);
        }
        ConfigCompiler::write_yaml('manifest.yml', $config);
        $this->logger->info("Done");
    }
}


