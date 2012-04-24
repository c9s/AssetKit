<?php
namespace AssetKit\Filter;

class CssRewriteFilter
{
	public $publicRoot;

	public function __construct($publicRoot)
	{
		$this->publicRoot = $publicRoot;
	}

	public function filter($collection)
	{
		$files = $collection->getFilePaths();
        foreach( $files as $file ) {

        }
	}

}


