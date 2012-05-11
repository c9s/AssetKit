<?php
namespace AssetKit\Compressor;
use AssetKit\CssMin;

class CssMinCompressor
{
	function compress($collection)
	{
		$content = $collection->getContent();
		$collection->setContent( CssMin::minify( $content ) );
	}
}

