<?php
namespace AssetKit;

class AssetLoader
{
	public $paths;

	function __construct($config,$paths = array())
	{
		$this->config = $config;
		$this->paths = $paths;
	}

	function load($name)
	{
		if( $this->config && $path = $this->config->getAssetPath($name) ) {
			$manifestFile = $path . DIRECTORY_SEPARATOR . 'manifest.yml';
			$m = new Manifest( $manifestFile );
			$m->config = $this->config;
			return $m;
		}
		else {
			foreach( $this->paths as $path ) {
				$manifestFile = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml';
				if( file_exists($manifestFile) ) {
					$m = new Manifest( $manifestFile );
					$m->config = $this->config;
					return $m;
				}
			}
		}
	}

}




