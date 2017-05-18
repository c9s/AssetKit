<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\JSMin;
use RuntimeException;

class JsMinPHPCompressor
{
    public function compress(Collection $collection)
    {
        $content = $collection->getContent();
        $collection->setContent(JSMin::minify($content));
    }
}



