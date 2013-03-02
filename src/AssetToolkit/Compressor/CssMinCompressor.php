<?php
namespace AssetToolkit\Compressor;
require dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;

class CssMinCompressor
{
    function compress($collection)
    {
        $content = $collection->getContent();
        $collection->setContent( CssMin::minify( $content ) );
    }
}

