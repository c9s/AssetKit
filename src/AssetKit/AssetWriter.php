<?php
namespace AssetKit;
use Exception;



/**
 * @class
 *
 * @code
 *   $writer = new AssetKit\AssetWriter( $config );
 *   $apc = new CacheKit\ApcCache(array( 'namespace' => uniqid() , 'default_expiry' => 3 ));
 *   $manifest = $writer->from( array($asset) )
 *       // ->cache( $apc )
 *       ->name( 'jqueryui' )
 *       ->in('assets') // public/assets
 *       ->write();
 * @code
 *
 * AssetLoader is only used for getPublicRoot (for writing)
 */
class AssetWriter
{
    public $in = 'assets';

    public $name;
    public $cache;

    public $config;

    protected $filters = array();

    protected $compressors = array();

    // filter builder
    protected $_filters = array();

    // compressor builder
    protected $_compressors = array();

    public $enableCompressor = true;

    public $environment = 'development';

    /**
     * Create with writer with config.
     *
     * @param AssetKit\Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    public function init()
    {
        $this->addCompressor('jsmin', function() {
            return new \AssetKit\Compressor\JsMinCompressor;
        });
        $this->addCompressor('cssmin', function() {
            return new \AssetKit\Compressor\CssMinCompressor;
        });

        $this->addCompressor('yui_css', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetKit\Compressor\Yui\CssCompressor($bin);
        });

        $this->addCompressor('yui_js', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetKit\Compressor\Yui\JsCompressor($bin);
        });

        $this->addFilter( 'coffeescript' ,function() {
            return new \AssetKit\Filter\CoffeeScriptFilter;
        });

        $this->addFilter( 'css_import', function() {
            return new \AssetKit\Filter\CssImportFilter;
        });

        $this->addFilter( 'css_rewrite', function() {
            // XXX:
            // return new AssetKit\Compressor
        });
    }


    /**
     * @param string $environment could be 'production' or 'development'
     */
    public function env($environment)
    {
        $this->environment = $environment;
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
        $this->_filters[ $name ] = $cb;
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




    public function runCollectionFilters($collection)
    {
        if( empty($collection->filters) )
            return;
        foreach( $collection->filters as $n ) {
            if( $filter = $this->getFilter( $n ) ) {
                $filter->filter($collection);
            }
            else {
                throw new Exception("filter $n not found.");
            }
        }
    }

    public function runCollectionCompressors($collection)
    {
        foreach( $collection->compressors as $n ) {
            if( $compressor = $this->getCompressor( $n ) ) {
                $compressor->compress($collection);
            }
            else { 
                throw new Exception("compressor $n not found.");
            }
        }
    }


    /**
     * Squash asset contents,
     * run through filters, compressors ...
     *
     * @param  AssetKit\Asset $asset
     * @return array [ css: string, js: string ]
     */
    public function squash($asset)
    {
        $js = '';
        $css = '';
        $collections = $asset->getFileCollections();
        foreach( $collections as $collection ) {
            $this->runCollectionFilters( $collection );

            // if we are in development mode, we don't need to compress them all.
            if( $this->environment !== 'development'
                    && $this->enableCompressor
                    && $collection->compressors ) {
                $this->runCollectionCompressors($collection);
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
        return array(
            'js' => $js,
            'css' => $css,
        );
    }

    /**
     * Squash stylesheet/javascript content from assets
     */
    public function squashThem($assets)
    {
        $css = '';
        $js = '';
        foreach( $assets as $asset ) {
            $ret = $this->squash( $asset );
            $css .= $ret['css'];
            $js  .= $ret['js'];
        }
        return array(
            'javascript' => $js,
            'stylesheet' => $css,
        );
    }

    public function write($assets)
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
                foreach( $assets as $asset ) {
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

        $contents = $this->squashThem( $assets );
        $manifest = array();
        $dir = $this->config->getPublicRoot(true);

        if( ! file_exists($dir . DIRECTORY_SEPARATOR . $this->in ) ) {
            mkdir( $dir . DIRECTORY_SEPARATOR . $this->in , 0755, true );
        }

        if( isset($contents['stylesheet']) && $contents['stylesheet'] ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-'
                . md5( $contents['stylesheet']) . '.css';

            $cssfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $cssfile , $contents['stylesheet'] ) !== false or die('write fail');

            $manifest['stylesheet'] = '/' . $path;
            $manifest['stylesheet_file'] = $cssfile;
        }
        if( isset($contents['javascript']) && $contents['javascript'] ) {
            $path = $this->in . DIRECTORY_SEPARATOR . $this->name . '-' 
                . md5( $contents['javascript']) . '.js';

            $jsfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $jsfile , $contents['javascript'] ) !== false or die('write fail');

            $manifest['javascript'] = '/' . $path;
            $manifest['javascript_file'] = $jsfile;
        }

        if( $this->name && $this->cache ) {
            $this->cache->set( 'asset-manifest:' . $this->name , $manifest );
        }
        return $manifest;
    }
}


