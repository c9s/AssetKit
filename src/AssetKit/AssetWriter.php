<?php
namespace AssetKit;
use Exception;

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

	public function name($as)
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
				$content = $collection->getContent();
				if( $collection->filters ) {
					foreach( $collection->filters as $filtername ) {
						if( $filter = $this->loader->getFilter( $filtername ) ) {
							$content = $filter->filter($content);
						}
						else {
							throw new Exception("filter $filtername not found.");
						}
					}
				}

				if( $this->loader->enableCompressor && $collection->compressors ) {
					foreach( $collection->compressors as $compressorname ) {
						if( $compressor = $this->loader->getCompressor( $compressorname ) ) {
							$content = $compressor->compress($content);
						}
						else { 
							throw new Exception("compressor $compressorname not found.");
						}
					}
				}

				if( $collection->isJavascript ) {
					$js .= $content;
				}
				elseif( $collection->isStylesheet ) {
					$css .= $content;
				}
				else {
					throw new Exception("Unknown asset type");
				}
			}
		}

		return array(
			'javascript' => $js,
			'stylesheet' => $css,
	   	);
	}
}


