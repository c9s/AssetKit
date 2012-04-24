<?php
namespace AssetKit;
use Exception;



/**
 * @class
 *
 */
class AssetWriter
{
    public $loader;
    public $assets;
    public $in;
    public $name;
    public $cache;

    protected $filters = array();

    protected $compressors = array();

    // filter builder
    protected $_filters = array();

    // compressor builder
    protected $_compressors = array();

    public $enableCompressor = true;

    public $environment = 'development';

    /**
     * Create with writer with a loader.
     *
     * @param AssetKit\AssetLoader $loader
     */
    public function __construct($loader)
    {
        $this->loader = $loader;
    }


    /**
     * @param string $environment could be 'production' or 'development'
     */
    public function env($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function from($assets)
    {
        $this->assets = $assets;
        return $this;
    }

    public function cache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function in($in)
    {
        $this->in = $in;
        return $this;
    }


    /**
     * Register filter builder
     *
     * @param string $name filter name
     * @param function $cb builder closure
     */
    public function addFilter($name,$cb)
    {
        $this->_filter[ $name ] = $cb;
    }


    /**
     * Register compressor
     *
     * @param string $name compressor name
     * @param function $cb function builder
     */
    public function addCompressor($name,$cb)
    {
        $this->_compressors[ $name ] = $cb;
    }


    /**
     * Get Filter object
     *
     * @param string $name filter name
     */
    public function getFilter($name)
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


    /**
     * Get compressor object
     *
     * @param string $name compressor name
     */
    public function getCompressor($name)
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


    /**
     * Aggregate stylesheet/javascript content
     *
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

                // if we are in development mode, we don't need to compress them all.
                if( $this->environment !== 'development'
                        && $this->enableCompressor
                        && $collection->compressors ) {
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
        // check mtime
        if( $this->name && $this->cache ) {
            if( $manifest = $this->cache->get( 'asset-manifest:' . $this->name ) ) {
                $jsmtime = isset($manifest['javascript_file']) 
                    ? filemtime( $manifest['javascript_file'] )
                    : null;
                $cssmtime = 
                    isset( $manifest['stylesheet_file'] ) 
                    ? filemtime( $manifest['stylesheet_file'] )
                    : null;

                // In development mode, we should check file stats.
                $expired = false;
                foreach( $this->assets as $asset ) {
                    $collections = $asset->getFileCollections();
                    foreach( $collections as $collection ) {
                        $mtime = $collection->getLastModifiedTime();

                        if( $collection->isJavascript 
                            && $jsmtime 
                            && $mtime > $jsmtime ) 
                        {
                            $expired = true;
                            break 2;
                        }
                        elseif( $collection->isStylesheet 
                                && $cssmtime 
                                && $mtime > $cssmtime )
                        {
                            $expired = true;
                            break 2;
                        }
                        else {
                            throw new Exception("Unknown type collection.");
                        }
                    }
                }

                // if the cache content is not expired, we can just return the content
                if( ! $expired ) {
                    return $contents;
                }
            }
        }


        $contents = $this->aggregate();
        $manifest = array();
        $dir = $this->loader->config->getPublicRoot();

        if( ! file_exists($dir . DIRECTORY_SEPARATOR . $this->in ) ) {
            mkdir( $dir . DIRECTORY_SEPARATOR . $this->in , 0755, true );
        }

        if( isset($contents['stylesheet']) ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-'
                . md5( $contents['stylesheet']) . '.css';

            $cssfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $cssfile , $contents['stylesheet'] ) !== false or die('write fail');

            $manifest['stylesheet_file'] = $cssfile;
            $manifest['stylesheet'] = DIRECTORY_SEPARATOR . $path;
        }
        if( isset($contents['javascript']) ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-' 
                . md5( $contents['javascript']) . '.js';

            $jsfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $jsfile , $contents['javascript'] ) !== false or die('write fail');

            $manifest['javascript'] = DIRECTORY_SEPARATOR . $path;
            $manifest['javascript_file'] = $jsfile;
        }

        if( $this->name && $this->cache ) {
            $this->cache->set( 'asset-manifest:' . $this->name , $manifest );
        }
        return $manifest;
    }
}


