<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\JSMin;
use RuntimeException;

class JsMinCompressor
{
    public $bin;

    public function __construct($bin = NULL)
    {
        if ($bin) {
            $this->bin = $bin;
        }
    }
    
    public function compress(Collection $collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();

        // If the bin is specified, we will run an external process to jsmin the content.
        if ($this->bin) {
            $proc = new Process(array($this->bin));
            $code = $proc->input($content)->run();
            if ( $code != 0 ) {
                throw new RuntimeException("JsminCompressor failure: $code");
            }
            $content = $proc->getOutput();
        } else {
            // Pure php jsmin
            $content = JSMin::minify( $content );
        }
        $collection->setContent($content);
    }
}



