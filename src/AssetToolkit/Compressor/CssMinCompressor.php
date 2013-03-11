<?php
namespace AssetToolkit\Compressor;
require_once dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;

class CssMinCompressor
{
    public function compress($collection)
    {
        $collection->setContent( CssMin::minify( 
            $collection->getContent()
        ));
    }
}

