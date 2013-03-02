<?php
namespace AssetToolkit\Compressor;
require_once dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;

class CssMinCompressor
{
    function compress($collection)
    {
        $content = $collection->getContent();
        $collection->setContent( CssMin::minify( $content ) );
    }
}

