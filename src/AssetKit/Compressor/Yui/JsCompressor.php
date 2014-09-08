<?php
namespace AssetKit\Compressor\Yui;
use AssetKit\Process;
use AssetKit\Collection;

class JsCompressor
{
    public $jar;
    public $java;
    public $charset;

    public function __construct($jar,$java = '/usr/bin/java')
    {
        $this->jar = $jar;
        $this->java = $java;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function compress(Collection $collection)
    { 
        $input = $collection->getContent();
        $proc = new Process( array( $this->java, '-jar', $this->jar ));
        $code = $proc->arg('--type')->arg('js')->input($input)->run();
        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}



