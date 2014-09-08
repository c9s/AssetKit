<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
require_once dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;

class CssMinCompressor
{
    public function compress(Collection $collection)
    {
        $collection->setContent( CssMin::minify( 
            $collection->getContent()
        ));
    }
}

