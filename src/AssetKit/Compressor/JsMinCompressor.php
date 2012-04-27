<?php
namespace AssetKit\Compressor;
use Symfony\Component\Process\ProcessBuilder;
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
            $pb = new ProcessBuilder(array( $this->bin ));
            $pb->setInput($content);
            $proc = $pb->getProcess();
            $code = $proc->run();
            $content = $proc->getOutput();
        }
        else {
            JSMin::minify( $content );
        }
        $collection->setContent($content);
    }
}



