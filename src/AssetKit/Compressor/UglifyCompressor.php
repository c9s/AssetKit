<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\Utils;
use RuntimeException;

class UglifyCompressor
{
    public $bin;

    public $node;

    public function __construct($bin = null, $node = null)
    {
        if ( $bin ) {
            $this->bin = $bin;
        } else {
            $this->bin = Utils::findbin('uglifyjs');
        }
        if ( $node ) {
            $this->node = $node;
        } else {
            $this->node = Utils::findbin('node');
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
            throw new RuntimeException("UglifyCompressor failure: $code");
        }

        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}



