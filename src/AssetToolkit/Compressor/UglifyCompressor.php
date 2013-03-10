<?php
namespace AssetToolkit\Compressor;
use AssetToolkit\Process;
use AssetToolkit\JSMin;
use AssetToolkit\Utils;
use RuntimeException;

class UglifyCompressor
{
    public $bin;

    public function __construct($bin = 'uglifyjs')
    {
        $this->bin = $bin;
    }
    
    public function compress($collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        $proc = new Process(array($this->bin));
        $code = $proc->input($content)->run();

        if ( $code != 0 ) {
            throw new RuntimeException("Process error: $code");
        }

        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}



