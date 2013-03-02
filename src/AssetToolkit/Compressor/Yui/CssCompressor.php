<?php
namespace AssetToolkit\Compressor\Yui;
use AssetToolkit\Process;

class CssCompressor
{
    public $jar;
    public $java;
    public $charset;

    function __construct($jar,$java = '/usr/bin/java')
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
        $input = $collection->getContent();

        $proc = new Process( array( $this->java, '-jar', $this->jar ));
        $code = $proc->arg('--type')->arg('css')->input($input)->run();
        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}

