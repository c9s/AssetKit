<?php
namespace AssetKit;

class AssetLoader
{
	public $paths;
	public $config;

	public $filters = array();

	public $compressors = array();

	// filter builder
	protected $_filters = array();

	// compressor builder
	protected $_compressors = array();

	function __construct($config,$paths = array())
	{
		$this->config = $config;
		$this->paths = $paths;
	}


	function addFilter($name,$cb)
	{
		$this->_filter[ $name ] = $cb;
	}

	function addCompressor($name,$cb)
	{
		$this->_compressors[ $name ] = $cb;
	}

	function getFilter($name)
	{
		if( isset($this->filters[$name]) )
			return $this->filters[$name];
		$cb = $this->_filters[ $name ];
		if( is_callable($cb) ) {
			return $this->filters[ $name ] = call_user_func($cb);
		}
		elseif( class_exists($cb,true) ) {
			return $this->filters[ $name ] = new $cb;
		}
	}

	function getCompressor($name)
	{
		if( isset($this->compressors[$name]) )
			return $this->compressors[$name];
		$cb = $this->_compressors[ $name ];
		if( is_callable($cb) ) {
			return $this->compressors[ $name ] = call_user_func($cb);
		}
		elseif( class_exists($cb,true) ) {
			return $this->compressors[ $name ] = new $cb;
		}
	}

	function load($name)
	{
		if( $this->config && $path = $this->config->getAssetPath($name) ) {
			$manifestFile = $path . DIRECTORY_SEPARATOR . 'manifest.yml';
			$m = new Manifest( $manifestFile );
			$m->config = $this->config;
			$m->loader = $this;
			return $m;
		}
		else {
			foreach( $this->paths as $path ) {
				$manifestFile = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'manifest.yml';
				if( file_exists($manifestFile) ) {
					$m = new Manifest( $manifestFile );
					$m->config = $this->config;
					$m->loader = $this;
					return $m;
				}
			}
		}
	}

}




