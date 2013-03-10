<?php
namespace AssetToolkit\Compressor;
require_once dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;

class CssMinCompressor
{
    public function compress($collection)
    {
        $content = $collection->getContent();
        $collection->setContent( CssMin::minify( $content ) );
    }
}

