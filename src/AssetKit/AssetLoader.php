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
		if( $config && $path = $this->config->getAssetPath($name) ) {
			$manifestFile = $path . DIRECTORY_SEPARATOR . 'manifest.php';
			return new Manifest( $manifestFile );
		}
		else {
			foreach( $this->paths as $path ) {
				$manifestFile = $path . DIRECTORY_SEPARATOR . 'manifest.php';
				if( file_exists($manifestFile) )
					return new Manifest( $manifestFile );
			}
		}
	}

}




