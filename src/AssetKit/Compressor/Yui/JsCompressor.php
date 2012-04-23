<?php
namespace AssetKit\Compressor;
use Symfony\Component\Process\ProcessBuilder;

class JsCompressor
{
    public $jar;
    public $java;
    public $charset;

    function __construct($jar,$java = 'java')
    {
        $this->jar = $jar;
        $this->java = $java;
    }

    function setCharset($charset)
    {
        $this->charset = $charset;
    }

    function compress($collection)
    { 
        $pb = new ProcessBuilder(array( $this->java, '-jar', $this->jar ));
        $input = $collection->getContent();
        $proc = $pb->getProcess();
        $proc->setInput($input);
        $code = $proc->run();
        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}



