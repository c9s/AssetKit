<?php
namespace AssetKit;

class AssetWriter
{
	public $loader;
	public $assets;
	public $as;
	public $in;

	public function __construct($loader)
	{
		$this->loader = $loader;
	}

	public function from($assets)
	{
		$this->assets = $assets;
		return $this;
	}

	public function as($as)
	{
		$this->as = $as;
		return $this;
	}

	public function in($in)
	{
		$this->in = $in;
		return $this;
	}

	public function write()
	{
		$css = '';
		$js = '';
		foreach( $this->assets as $asset ) {
			$collections = $asset->getFileCollections();
			foreach( $collections as $collection ) {
				if( $collection->filters ) {
					foreach( $collection->filters as $filtername ) {
						$filter = $loader->getFilter( $filtername );
						$contents = $filter->read( $collection->getContent() );
					}
				}

				if( $this->loader->enableCompressor ) {
					if( $collection->compressors ) {
						$collection->getFiles();
					}
				}

			}
		}
	}
}


