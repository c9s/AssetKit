<?php
namespace AssetToolkit\Compressor;
use AssetToolkit\Process;
use AssetToolkit\JSMin;

class JsMinCompressor
{
    public $bin;

    public function __construct($bin = null)
    {
        if ($bin) {
            $this->bin = $bin;
        }
    }
    
    public function compress($collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        if ( $this->bin ) {
            $proc = new Process(array($this->bin));
            $code = $proc->input($content)->run();
            $content = $proc->getOutput();
        } elseif ( extension_loaded('jsmin') ) {
            $content = jsmin( $content );
        }
        else {
            // pure php jsmin
            $content = JSMin::minify( $content );
        }
        $collection->setContent($content);
    }
}



