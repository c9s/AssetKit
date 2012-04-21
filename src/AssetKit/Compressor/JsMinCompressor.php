<?php
namespace AssetKit\Compressor;
use AssetKit\JSMin;

class JsMinCompressor
{
    function compress($collection)
    {
        $content = $collection->getContent();
        $collection->setContent( JSMin::minify( $content ) );
    }
}



