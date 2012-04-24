<?php
namespace AssetKit\Compressor\Yui;
use Symfony\Component\Process\ProcessBuilder;

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
        $pb = new ProcessBuilder(array( $this->java, '-jar', $this->jar ));
        $pb->add('--type')->add('css');
        $pb->setInput($input);

        $proc = $pb->getProcess();
        $code = $proc->run();

        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}

