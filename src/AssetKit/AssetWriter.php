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

    /**
     * for production mode, 
     * check squashed file mtime and source files
     *
     * You can turn off this to increase performance (about 11ms on iMac)
     */
    public $checkExpiry = true;

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

        $this->addFilter( 'sass' , function() {
            return new \AssetKit\Filter\SassFilter;
        });

        $this->addFilter( 'scss' , function() {
            return new \AssetKit\Filter\ScssFilter;
        });

        $this->addFilter( 'css_rewrite', function() {
            return new \AssetKit\Filter\CssRewriteFilter;
        });


        /**
         * XXX:
         
         // convert sass file to css (using sass filter), replace .sass with .css extension
         $this->addPatternFilter( '.sass' , '.css' , 'sass' );
         $this->addPatternFilter( '.scss' , '.css' , 'scss' );
         */


    }


    /**
     * @param string $environment could be 'production' or 'development'
     */
    public function env($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function production()
    {
        $this->environment = 'production';
        return $this;
    }

    public function development()
    {
        $this->environment = 'development';
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

    public function getFilters()
    {
        $self = $this;
        return array_map(function($n) use ($self) { 
            return $self->getFilter($n);
                }, $this->_filters);
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

    public function getCompressors()
    {
        $self = $this;
        return array_map(function($n) use($self) { 
            return $self->getCompressor($n);
             }, $this->_compressors);
    }



    public function runCollectionFilters($collection)
    {
        if( empty($collection->filters) )
            return;

        if( $collection->hasFilter('no') )
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
        // if custom compresor is not define, use default compressors
        if( empty($collection->compressors) ) {
            if( $collection->isJavascript || $collection->isCoffeescript ) {
                $jsmin = new Compressor\JsMinCompressor;
                $jsmin->compress($collection);
            }
            elseif( $collection->isStylesheet ) {
                $cssmin = new Compressor\CssMinCompressor;
                $cssmin->compress($collection);
            }
        }
        else {
            if( $collection->hasCompressor('no') )
                return;

            foreach( $collection->compressors as $n ) {
                if( $compressor = $this->getCompressor( $n ) ) {
                    $compressor->compress($collection);
                }
                else { 
                    throw new Exception("compressor $n not found.");
                }
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

            // skip unknown types
            if( ! $collection->isJavascript && ! $collection->isStylesheet )
                continue;


            // if we are in development mode, we don't need to compress them all.
            if( $this->environment === 'production'
                    && $this->enableCompressor ) 
            {
                // for stylesheets, before compress it, we should import the css contents
                if( $collection->isStylesheet ) {
                    $import = new Filter\CssImportFilter;
                    $import->filter( $collection );
                }
                elseif( $collection->isCoffeescript ) {
                    $coffee = new Filter\CoffeeScriptFilter;
                    $coffee->filter( $collection );
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                $this->runCollectionFilters( $collection );
            }

            if( $collection->isJavascript ) {
                $js .= $collection->getContent();
            } 
            elseif( $collection->isStylesheet ) {
                $css .= $collection->getContent();
            }
        }
        return array(
            'js' => $js,
            'css' => $css,
        );
    }

    /**
     * Squash stylesheet/javascript content from assets
     *
     * @return array [ javascript => string , stylesheet => string ]
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
            'js' => $js,
            'css' => $css,
        );
    }

    public function write($assets)
    {
        if( $this->environment === 'production' ) {
            return $this->writeForProduction($assets);
        }
        elseif( $this->environment === 'development' ) {
            return $this->writeForDevelopment($assets);
        }
        else {
            throw new Exception("Unknown environment type: {$this->env}");
        }
    }


    /**
     * Write asset for development mode,
     *
     * In development mode, we don't compress any asset files.
     *
     * 1. Read asset files from source directory.
     * 2. If there is no filters, just return the file list (with file types)
     * 3. For collections that has filters (like coffeescript or sass),
     *    Run through the filters , and replace file extensions (for 
     *    coffeescript or sass) to js or css
     * 3. Separate stylesheet and javascript files and return.
     *
     * @param array $assets
     */
    public function writeForDevelopment($assets)
    {
        $manifest = array(
            'javascripts' => array(),
            'stylesheets' => array(),
        );
        foreach( $assets as $asset ) {
            $publicDir = $asset->getPublicDir(true);
            $baseUrl   = $asset->getBaseUrl();

            foreach( $asset->getFileCollections() as $c ) {
                $paths = $c->getFilePaths();


                // for collections has filters, pipe content through these filters.
                if( $filters = $c->getFilters() ) {
                    $this->runCollectionFilters($c);

                    $content = $c->getContent();

                    if( $c->isCoffeescript ) {
                        $newpath = str_replace( '.coffee' , '.js' , $paths[0] );
                        $path = $publicDir . DIRECTORY_SEPARATOR . $newpath;
                        $url  = $baseUrl . '/' . $newpath;
                        file_put_contents( $path , $content) or die("write fail.");
                        $manifest['javascripts'][] = array( 'path' => $path, 'url'  => $url, 'attrs' => array() );
                    }
                    elseif( $c->isStylesheet ) {
                        $info = pathinfo($paths[0]);
                        $newpath = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '-filtered.css';
                        $path = $publicDir . DIRECTORY_SEPARATOR . $newpath;
                        $url  = $baseUrl . '/' . $newpath;
                        file_put_contents( $path , $content) or die("write fail.");
                        $manifest['stylesheets'][] = array( 'path' => $path, 'url'  => $url, 'attrs' => array() );
                    }
                    elseif( $c->isJavascript ) {
                        $newpath = str_replace( '.js' , '-filtered.js' , $paths[0] );
                        $path = $publicDir . DIRECTORY_SEPARATOR . $newpath;
                        $url  = $baseUrl . '/' . $newpath;
                        file_put_contents( $path , $content) or die("write fail.");
                        $manifest['javascripts'][] = array( 'path' => $path, 'url'  => $url, 'attrs' => array() );
                    }
                }
                else {
                    $k = null;
                    if( $c->isJavascript || $c->isCoffeescript )
                        $k = 'javascripts';
                    elseif( $c->isStylesheet )
                        $k = 'stylesheets';

                    // XXX: we should refactor this, for other generic filters
                    // if it's coffee, we should squash this collection
                    if( $c->isCoffeescript ) {
                        $coffee = new Filter\CoffeeScriptFilter;
                        $coffee->filter( $c );
                        $content = $c->getContent();

                        $newpath = str_replace( '.coffee' , '.js' , $paths[0] );
                        // put content and append into manifest
                        $path = $publicDir . DIRECTORY_SEPARATOR . $newpath;
                        $url  = $baseUrl . '/' . $newpath;
                        file_put_contents( $path , $content) or die("write fail");
                        $manifest['javascripts'][] = array(
                            'path' => $path,
                            'url'  => $url,
                            'attrs' => array(),
                        );
                    }
                    else if( $c->isStylesheet || $c->isJavascript ) {
                        foreach( $paths as $path ) {
                            $manifest[$k][] = array(
                                'path' => $publicDir . DIRECTORY_SEPARATOR . $path,
                                'url'  => $baseUrl   . '/' . $path,
                                'attrs' => array(),
                            );
                        }
                    }
                }
            }
        }
        return $manifest;
    }


    /**
     * Squash assets and return a manifest.
     *
     * @param Asset[] $assets
     *
     * @return array manifest
     */
    public function writeForProduction($assets)
    {
        // check mtime
        if( $this->name && $this->cache ) {
            if( $manifest = $this->cache->get( 'asset-manifest:' . $this->name ) ) {
                if( ! $this->checkExpiry )
                    return $manifest;

                // find the last modified time of squashed files
                $jsmtime  = $this->cache->get( 'asset-manifest-jsmtime:' . $this->name ) ?: 0;
                $cssmtime = $this->cache->get( 'asest-manifest-cssmtime:' . $this->name ) ?: 0;

                if( $jsmtime == 0 || $cssmtime == 0 ) {
                    foreach( array('javascripts','stylesheets') as $t ) {
                        foreach( $manifest[$t] as $file ) {
                            $path = $file['path'];
                            $mtime = filemtime($path);
                            if( $mtime > $jsmtime ) {
                                if( 'javascripts' === $t ) {
                                    $jsmtime = $mtime;
                                } elseif( 'stylesheets' === $t ) {
                                    $cssmtime = $mtime;
                                }
                            }
                        }
                    }
                }

                // We should check file stats to update squshed files.
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
                    }
                }

                // if the cache content is not expired, we can just return the content
                if( ! $expired ) {
                    return $manifest;
                }
            }
        }

        // die('squash...');

        // squash new content from assets
        $contents = $this->squashThem( $assets );
        $manifest = array(
            'stylesheets' => array(),
            'javascripts' => array(),
        );
        $dir = $this->config->getPublicRoot(true); // public web root

        if( ! file_exists($dir . DIRECTORY_SEPARATOR . $this->in ) ) {
            mkdir( $dir . DIRECTORY_SEPARATOR . $this->in , 0755, true );
        }

        if( isset($contents['css']) && $contents['css'] ) {
            $path = $this->in . DIRECTORY_SEPARATOR 
                . ($this->name ? $this->name . '-' . md5( $contents['css']) : md5( $contents['css'] ) )
                . '.css';

            $cssfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $cssfile , $contents['css'] ) !== false 
                or die('write fail');

            $manifest['stylesheets'][] = array( 
                'url' => '/' . $path,
                'path' => $cssfile,
                'attrs' => array(), /* css attributes, keep for future. */
            );
        }
        if( isset($contents['js']) && $contents['js'] ) {
            $path = $this->in . DIRECTORY_SEPARATOR 
                . ($this->name ? $this->name . '-' . md5( $contents['js']) : md5( $contents['js'] ))
                . '.js';

            $jsfile = $dir . DIRECTORY_SEPARATOR . $path;
            file_put_contents( $jsfile , $contents['js'] ) !== false 
                    or die('write fail');

            $manifest['javascripts'][] = array(
                'url' => '/' . $path,
                'path' => $jsfile,
                'attrs' => array(),
            );
        }

        if( $this->name && $this->cache ) {
            $this->cache->set( 'asset-manifest:' . $this->name , $manifest );
        }
        return $manifest;
    }
}


