<?php
namespace AssetKit\Compressor;
use CssMinifier;
use CssMin;

class CssMinCompressor
{
	function compress($collection)
	{
		$content = $collection->getContent();
		$collection->setContent( CssMin::minify( $content ) );
	}
}

