<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\JSMin;
use RuntimeException;

class JsMinCompressor
{
    public $bin;

    public function __construct($bin = null)
    {
        if ($bin) {
            $this->bin = $bin;
        }
    }
    
    public function compress(Collection $collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        if (extension_loaded('jsmin')) {
            $content = jsmin( $content );
        } elseif ($this->bin) {
            $proc = new Process(array($this->bin));
            $code = $proc->input($content)->run();
            if ( $code != 0 ) {
                throw new RuntimeException("JsminCompressor failure: $code");
            }
            $content = $proc->getOutput();
        } else {
            // pure php jsmin
            $content = JSMin::minify( $content );
        }
        $collection->setContent($content);
    }
}



