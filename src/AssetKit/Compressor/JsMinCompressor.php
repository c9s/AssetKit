<?php
namespace AssetKit\Compressor;
use AssetKit\Process;
use AssetKit\JSMin;

class JsMinCompressor
{
    public $bin;

    function __construct($bin = null)
    {
        $this->bin = $bin;
    }
    
    function compress($collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        if( $this->bin ) {
            $proc = new Process(array($this->bin));
            $code = $proc->input($content)->run();
            $content = $proc->getOutput();
        }
        else {
            JSMin::minify( $content );
        }
        $collection->setContent($content);
    }
}



