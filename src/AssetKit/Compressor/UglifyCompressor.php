<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\Utils;
use RuntimeException;
use Exception;

class UglifyCompressor
{
    public $bin;

    public $node;

    public function __construct($bin = null, $node = null)
    {
        $this->bin = $bin ?: Utils::findbin('uglifyjs');
        $this->node = $node ?: Utils::findbin('node');
        if (!$this->bin) {
            throw new Exception('uglifyjs not found.');
        }
    }
    
    public function compress(Collection $collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        $proc = new Process(array($this->node, $this->bin));
        $proc->arg('-');
        $proc->input($content);

        $code = $proc->run();
        if ( $code != 0 ) {
            $command = $proc->getCommand();
            throw new RuntimeException("UglifyCompressor failure: ($code) " . $proc->getError() . " command: $command, collection: " . $collection->sourceDir );
        }
        $output = $proc->getOutput();
        $collection->setContent($output);
    }
}



