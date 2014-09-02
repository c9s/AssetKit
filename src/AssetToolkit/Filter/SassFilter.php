<?php
namespace AssetToolkit\Filter;
use AssetToolkit\Collection;
use AssetToolkit\Process;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetUrlBuilder;
use RuntimeException;
use AssetToolkit\Utils;

use Symfony\Component\Process\ProcessBuilder;

class SassFilter extends BaseFilter
{
    public $bin;

    public $fromFile = true;

    public $loadPaths = array();

    public $enableCompass = true;

    public $style;

    public $rewrite = true;

    public $debug = false;

    public function __construct(AssetConfig $config, $bin = null)
    {
        if ( $bin ) {
            $this->bin = $bin;
        } else {
            $this->bin = Utils::findbin('sass');
        }
        parent::__construct($config);
    }

    public function setDebug($bool)
    {
        $this->debug = $bool;
    }

    public function setCompass($bool)
    {
        $this->enableCompass = $bool;
    }

    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }


    /**
     * Set SASS output style
     *
     * @param string $style compact, compressed, or expanded.
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function createProcess()
    {
        $proc = new Process(array( $this->bin ));

        if ($this->enableCompass) {
            $proc->arg('--compass');
        }

        foreach( $this->loadPaths as $path ) {
            $proc->arg('--load-path');
            $proc->arg($path);
        }

        if ( $this->debug ) {
            $proc->arg('--debug-info');
        }

        if ( $this->style ) {
            $proc->arg('--style')->arg($this->style);
        }
        return $proc;
    }

    public function filter(Collection $collection)
    {
        if ($collection->filetype !== Collection::FILETYPE_SASS) {
            return;
        }

        $urlBuilder = new AssetUrlBuilder($this->config);
        $assetBaseUrl = $urlBuilder->buildBaseUrl($collection->asset);

        $chunks = $collection->getChunks();
        foreach( $chunks as &$chunk ) {
            $proc = $this->createProcess();
            $proc->arg('--load-path');
            $proc->arg( dirname($chunk['fullpath']) );
            $proc->arg('-s'); // use stdin
            $proc->input($chunk['content']);

            // echo $proc->getCommand();
            $code = $proc->run();
            if ( $code != 0 ) {
                throw new RuntimeException("SassFilter failure: $code. Command: " . $proc->getCommand() . " Output:" . $proc->getOutput());
            }
            $output = $proc->getOutput();

            if ( $this->rewrite ) {
                $rewrite = new CssRewriteFilter($this->config, $assetBaseUrl);
                $output = $rewrite->rewrite( $output, $assetBaseUrl . '/' . dirname($chunk['path']) );
            }

            $chunk['content'] = $output;
        }
        $collection->setChunks($chunks);
    }

}

