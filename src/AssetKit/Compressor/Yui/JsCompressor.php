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

		$content = $collection->getContent();
		$collection->setContent( CssMin::minify( $content ) );
	}
}



