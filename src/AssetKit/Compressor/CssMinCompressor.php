<?php
namespace AssetKit\Compressor;
require_once dirname(dirname(__FILE__)) . '/CssMin.php';
use CssMin;
use AssetKit\Collection;

class CssMinCompressor
{
    public function compress(Collection $collection)
    {
        $collection->setContent( CssMin::minify( 
            $collection->getContent()
        ));
    }
}

