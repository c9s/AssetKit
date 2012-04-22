<?php
namespace AssetKit;
use Exception;

class AssetWriter
{
    public $loader;
    public $assets;
    public $in;
    public $name;
    public $publicDir;


	public $filters = array();

	public $compressors = array();

	// filter builder
	protected $_filters = array();

	// compressor builder
	protected $_compressors = array();

	public $enableCompressor = true;

    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    public function from($assets)
    {
        $this->assets = $assets;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function publicDir($dir)
    {
        $this->publicDir = rtrim($dir, DIRECTORY_SEPARATOR);
        return $this;
    }

    public function in($in)
    {
        $this->in = $in;
        return $this;
    }


    /**
     * Aggregate stylesheet/javascript content
     */
    public function aggregate()
    {
        $css = '';
        $js = '';
        foreach( $this->assets as $asset ) {
            $collections = $asset->getFileCollections();
            foreach( $collections as $collection ) {
                if( $collection->filters ) {
                    foreach( $collection->filters as $filtername ) {
                        if( $filter = $this->getFilter( $filtername ) ) {
                            $filter->filter($collection);
                        }
                        else {
                            throw new Exception("filter $filtername not found.");
                        }
                    }
                }

                if( $this->enableCompressor && $collection->compressors ) {
                    foreach( $collection->compressors as $compressorname ) {
                        if( $compressor = $this->getCompressor( $compressorname ) ) {
                            $compressor->compress($collection);
                        }
                        else { 
                            throw new Exception("compressor $compressorname not found.");
                        }
                    }
                }

                if( $collection->isJavascript ) {
                    $js .= $collection->getContent();
                } elseif( $collection->isStylesheet ) {
                    $css .= $collection->getContent();
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


    public function write()
    {
        $contents = $this->aggregate();
        $return = array();
        $dir = $this->loader->config->baseDir;

        if( ! file_exists($dir) ) {
            mkdir( $dir , 0755, true );
        }

        if( isset($contents['stylesheet']) ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-' 
                    . md5( $contents['stylesheet']) . '.css';

            $cssfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $cssfile , $contents['stylesheet'] ) !== false or die('write fail');

            $return['stylesheet_file'] = $cssfile;
            $return['stylesheet'] = DIRECTORY_SEPARATOR . $path;
        }
        if( isset($contents['javascript']) ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-' 
                    . md5( $contents['javascript']) . '.js';
            
            $jsfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $jsfile , $contents['javascript'] ) !== false or die('write fail');

            $return['javascript'] = DIRECTORY_SEPARATOR . $path;
            $return['javascript_file'] = $jsfile;
        }
        return $return;
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


		if( ! isset($this->_filters[$name]) )
			return;

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

		if( ! isset($this->_compressors[$name]) )
			return;

		$cb = $this->_compressors[ $name ];
		if( is_callable($cb) ) {
			return $this->compressors[ $name ] = call_user_func($cb);
		}
		elseif( class_exists($cb,true) ) {
			return $this->compressors[ $name ] = new $cb;
		}
	}

}


