<?php
namespace AssetToolkit;
use Exception;
use RuntimeException;
use AssetToolkit\FileUtil;

class AssetCompilerException extends Exception {  }

class AssetCompiler
{

    /**
     * @var AssetToolkit\AssetConfig asset config object.
     */
    public $config;


    /**
     * @var AssetToolkit\AssetLoader asset loader object.
     */
    public $loader;

    /**
     * @var boolean enable fstat check in production mode.
     *
     * You can simply restart your fpm or apache server to reset 
     * the APC cache. or enable this option to check fstat in 
     * every request.
     *
     * We prefer clean up manifest cache manually, because fstat checking
     * might consume a lot of I/O.
     */
    public $productionFstatCheck = false;


    /**
     * @var boolean enable compressor in production mode
     */
    public $enableCompressor = true;

    /**
     * @var array cached filters
     */
    protected $filters = array();


    /**
     * @var array cached compressors
     */
    protected $compressors = array();

    /**
     * @var array filter build
     */
    protected $_filters = array();

    /**
     * @var array compressor builder
     */
    protected $_compressors = array();


    public $checksumAlgo = 'md5';


    public $defaultJsCompressor = 'jsmin';


    public $defaultCssCompressor = 'cssmin';


    public function __construct($config,$loader)
    {
        $this->config = $config;
        $this->loader = $loader;
    }

    public function setProductionFstatCheck($b)
    {
        $this->productionFstatCheck = $b;
    }

    public function enableProductionFstatCheck()
    {
        $this->productionFstatCheck = true;
    }



    public function setConfig(AssetConfig $config)
    {
        $this->config = $config;
    }


    public function setLoader(AssetLoader $loader)
    {
        $this->loader = $loader;
    }

    public function registerDefaultCompressors()
    {
        $this->registerCompressor('jsmin', '\AssetToolkit\Compressor\JsMinCompressor');
        $this->registerCompressor('cssmin', '\AssetToolkit\Compressor\CssMinCompressor');
        $this->registerCompressor('uglifyjs', '\AssetToolkit\Compressor\UglifyCompressor');

        $this->registerCompressor('yui_css', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetToolkit\Compressor\Yui\CssCompressor($bin);
        });

        $this->registerCompressor('yui_js', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetToolkit\Compressor\Yui\JsCompressor($bin);
        });
    }

    public function registerDefaultFilters()
    {
        $this->registerFilter( 'coffeescript','\AssetToolkit\Filter\CoffeeScriptFilter');
        $this->registerFilter( 'css_import', '\AssetToolkit\Filter\CssImportFilter');
        $this->registerFilter( 'sass', '\AssetToolkit\Filter\SassFilter');
        $this->registerFilter( 'scss', '\AssetToolkit\Filter\ScssFilter');
        $this->registerFilter( 'css_rewrite', '\AssetToolkit\Filter\CssRewriteFilter');
    }




    /**
     * Set checksum algorithm for generating content checksum
     */
    public function setChecksumAlgorithm($algo)
    {
        $this->checksumAlgo = $algo;
    }

    /**
     * Simply run filters through these assets.
     *
     * @param AssetToolkit\Assets[] asset objects
     */
    public function compileAssetsForDevelopment($assets, $target = null)
    {
        $assets = (array)$assets;
        $assetNames = array();
        $out = array();

        $root = $this->config->getRoot();
        $baseDir = $this->config->getBaseDir(true);
        $baseUrl = $this->config->getBaseUrl();

        foreach( $assets as $asset ) {
            $assetNames[] = $asset->name;
            $assetBaseUrl = $baseUrl . '/' . $asset->name;
            foreach( $asset->getCollections() as $c ) {

                $type = null;
                if ( $c->isCoffeescript || $c->isJavascript ) {
                    $type = 'javascript';
                } elseif ( $c->isStylesheet ) {
                    $type = 'stylesheet';
                } else {
                    // skip non-filetype collections
                    continue;
                }

                // for collections has filters, 
                // pipe content through these filters.
                $filtered = false;

                // if user defined filters, run it.
                if ( $filters = $c->getFilters() ) {
                    $filtered = $this->runUserDefinedFilters($c);
                } else {
                    $filtered = $c->runDefaultFilters();
                }

                // for coffee-script we need to pass the coffee-script to compiler
                // and get the javascript from the output, we can simply render the 
                // content in the pipe.
                if ( $filtered ) {
                    $content = $c->getContent();
                    $out[] = array(
                        'type' => $type,
                        'content' => $content,
                        'attrs' => $c->attributes
                    );
                } else {
                    $paths = $c->getFilePaths();
                    foreach( $paths as $path ) {
                        $out[] = array( 
                            'type' => $type, 
                            'url' => $assetBaseUrl . '/' . $path,
                            'attrs' => $c->attributes,
                        );
                    }
                }
            }
        }

        // if we got target name, then we should register the target to the assetkit config.
        if ( $target ) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->config->addTarget($target, $assetNames);
            $this->config->save();
        }


        return $out;
    }


    /**
     * Compile single asset
     * This is for production mode.
     *
     * For example:
     *
     * baseDir: public/assets
     * baseUrl: /assets
     *
     * And the asset directory:
     *
     * assets/jquery
     * assets/jquery/manifest.yml
     * assets/jquery/jquery-1.8.2.js
     *
     * Will be compiled into:
     *
     * public/assets/jquery/jquery.min.js
     *
     * @return array
     *
     *    {
     *      css: [string] minified css content.
     *      js:  [string] minified js content.
     *      css_file: [string] minified css file.
     *      js_file:  [string] minified js file.
     *      css_url: [string] minified css url.
     *      js_url:  [string] minified js url.
     *      mtime: [integer] the last modification time.
     *    }
     *
     */
    public function compile($asset, $force = false) 
    {
        $cacheKey = $this->config->getNamespace() . ':' . $asset->name;

        if ( ! $force && $this->config->cache ) {
            $cache = $this->config->cache->get($cacheKey);

            // cache validation
            if ( $cache ) {
                if ( ! $this->productionFstatCheck ) {
                    return $cache;
                } else {
                    $upToDate = true;
                    if ( $mtime = @$cache['mtime'] ) {
                        if ( $asset->isOutOfDate($mtime) ) {
                            $upToDate = false;
                        }
                    }
                    if ( $upToDate ) {
                        return $cache;
                    }
                }
            }
        }

        $out = $this->squash($asset);

        // get the absolute path of install dir.
        $baseUrl    = $asset->getBaseUrl();
        $name = $asset->name . '.min';

        $compiledDir = $this->prepareCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();

        $jsFile = $compiledDir . DIRECTORY_SEPARATOR . $name . '.js';
        $cssFile = $compiledDir . DIRECTORY_SEPARATOR . $name . '.css';
        $jsUrl = $compiledUrl . "/$name.js";
        $cssUrl = $compiledUrl . "/$name.css";

        if ($out['js']) {
            $out['js_file'] = $jsFile;
            $out['js_url'] = $jsUrl;
            $this->writeFile( $jsFile, $out['js'] );
        }
        if ($out['css']) {
            $out['css_file'] = $cssFile;
            $out['css_url'] = $cssUrl;
            $this->writeFile( $cssFile , $out['css'] );
        }

        if ( $this->config->cache ) {
            $this->config->cache->set($cacheKey, $out);
        }
        return $out;
    }


    /**
     * Compile multiple assets into the target path.
     *
     * For example, compiling:
     *
     *    - jquery
     *    - jquery-ui
     *    - blueprint
     *
     * Which generates
     *
     *   /assets/{target}/{md5}.min.css
     *   /assets/{target}/{md5}.min.js
     *
     * The compiled manifest is stored in APC or in the file cache.
     * So that if the touch time stamp is updated. AssetCompiler 
     * will re-compile these stuff.
     *
     * @param string target name
     * @param array Asset[]
     */
    public function compileAssetsForProduction($assets, $target = '', $force = false)
    {
        $hasTarget = $target ? true : false;
        if ( $target ) {
            $cacheKey = $this->config->getNamespace() . ':' . $target;
        } else {
            $cacheKey = $this->config->getNamespace() . ':' . $this->_generateCacheKeyFromAssets($assets);
            $target = $this->config->getDefaultTarget();
        }


        if ( $this->config->cache ) {
            $cache = $this->config->cache->get($cacheKey);

            // cache validation
            if ( $cache && ! $force ) {
                if ( $this->productionFstatCheck ) {
                    $upToDate = true;
                    if ( $mtime = @$cache['mtime'] ) {
                        foreach( $assets as $asset ) {
                            if ( $asset->isOutOfDate($mtime) ) {
                                $upToDate = false;
                                break;
                            }
                        }
                    }
                    if ( $upToDate )
                        return $cache;
                } else {
                    return $cache;
                }
            }
        }

        $contents = array( 'js' => '', 'css' => '' );
        $assetNames = array();
        foreach( $assets as $asset ) {
            $assetNames[] = $asset->name;

            // get manifest after compiling
            $m = $this->compile($asset, $force);

            // concat results from manifest
            if (isset($m['js_file']) ) {
                $contents['js'] .= file_get_contents($m['js_file']);
            }
            if (isset($m['css_file']) ) {
                $contents['css'] .= file_get_contents($m['css_file']);
            }
        }

        // register target (assets) to the config, if it's not defaultTarget,
        if ( $hasTarget ) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->config->addTarget($target, $assetNames);
            $this->config->save();
        }

        $compiledDir = $this->prepareCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $outfiles = array();

        // write minified results to file
        if ($contents['js']) {
            $outfiles['js_checksum'] = hash($this->checksumAlgo, $contents['js']);
            $outfiles['js_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['js_checksum'] . '.min.js';
            $outfiles['js_url']  = "$compiledUrl/$target-" . $outfiles['js_checksum']  . '.min.js';
            $this->writeFile( $outfiles['js_file'], $contents['js'] );
        }

        if ($contents['css']) {
            $outfiles['css_checksum'] = hash($this->checksumAlgo, $contents['css']);
            $outfiles['css_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['css_checksum'] . '.min.css';
            $outfiles['css_url'] = "$compiledUrl/$target-" . $outfiles['css_checksum'] . '.min.css';
            $this->writeFile( $outfiles['css_file'], $contents['css'] );
        }


        $outfiles['assets']  = $assetNames;
        $outfiles['mtime']   = time();
        $outfiles['cache_key'] = $cacheKey;
        $outfiles['target'] = $target;

        $outfiles['metafile'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '.meta';
        $this->writeFile( $outfiles['metafile'], serialize($outfiles) );

        if ( $this->config->cache ) {
            $this->config->cache->set($cacheKey, $outfiles);
        }
        return $outfiles;
    }


    protected function _generateCacheKeyFromAssets($assets)
    {
        $names = array();
        foreach($assets as $a) {
            $names[] = $a->name;
        }
        sort($names);
        $key = join('-',$names);

        if ( strlen($key) < 64 ) {
            return 'autogenerated-' . $key;
        }
        // we don't need so much accuracy here, 
        // simply use crc32 is faster than md5
        return 'autogenerated-' . crc32($key);
    }

    public function clean($m)
    {
        foreach( array('css_file','js_file')  as $k ) {
            if ( $m[$k] ) {
                futil_unlink_if_exists( $m[$k] );
            }
        }
    }

    public function prepareCompiledDir()
    {
        $compiledDir = $this->config->getCompiledDir();
        futil_mkdir_if_not_exists($compiledDir,0766, true);

        if ( ! is_writable($compiledDir) ) {
            throw new AssetCompilerException("The $compiledDir is not writable.");
        }
        return $compiledDir;
    }


    public function writeFile($path,$content) 
    {
        if ( false === file_put_contents($path, $content) ) {
            throw new AssetCompilerException("Can not write $path");
        }
    }



    /**
     * Register filter builder
     *
     * @param string $name filter name
     * @param function $cb builder closure
     */
    public function registerFilter($name,$cb)
    {
        $this->_filters[ $name ] = $cb;
    }


    /**
     * Register compressor
     *
     * @param string $name compressor name
     * @param function $cb function builder
     */
    public function registerCompressor($name,$cb)
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
        if ( isset($this->filters[$name]) )
            return $this->filters[$name];

        if ( ! isset($this->_filters[$name]) )
            return;

        $cb = $this->_filters[ $name ];
        if ( is_callable($cb) ) {
            return $this->filters[ $name ] = call_user_func($cb);
        } elseif ( class_exists($cb,true) ) {
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
        if ( isset($this->compressors[$name]) )
            return $this->compressors[$name];

        // check compressor builder
        if ( ! isset($this->_compressors[$name]) )
            return;

        $cb = $this->_compressors[ $name ];

        if ( is_string($cb) ) {
            if ( class_exists($cb,true) ) {
                return $this->compressors[ $name ] = new $cb;
            } else {
                throw new AssetCompilerException("$cb class not found.");
            }
        } else if ( is_callable($cb) ) {
            return $this->compressors[ $name ] = call_user_func($cb);
        } else {
            throw new AssetCompilerException("Unsupported compressor builder");
        }
    }

    public function getCompressors()
    {
        $self = $this;
        $c = array();
        return array_map(function($n) use($self) { 
            return $self->getCompressor($n);
             }, $this->_compressors);
    }


    /**
     * Run user-defined filters
     */
    public function runUserDefinedFilters($collection)
    {
        if ( empty($this->filters) )
            return false;
        if ( $this->hasFilter('no') )
            return false;

        foreach( $this->filters as $n ) {
            if ( $filter = $this->getFilter( $n ) ) {
                $filter->filter($collection);
                return true;
            } else {
                throw new AssetCompilerException("filter $n not found.");
            }
        }
        return false;
    }

    /**
     * Squash asset contents,
     * run through filters, compressors ...
     *
     * @param  AssetToolkit\Asset $asset
     * @return array [ css: string, js: string ]
     */
    public function squash($asset)
    {
        $out = array(
            'js' => '',
            'css' => '',
            'mtime' => 0,
        );
        $collections = $asset->getCollections();
        foreach( $collections as $collection ) {

            // skip unknown types
            if ( ! $collection->isJavascript && ! $collection->isStylesheet && ! $collection->isCoffeescript )
                continue;

            if ( $lastm = $collection->getLastModifiedTime() ) {
                if ( $lastm > $out['mtime'] ) {
                    $out['mtime'] = $lastm;
                }
            }

            // if we are in development mode, we don't need to compress them all,
            // we just filter them
            if ( $this->enableCompressor ) 
            {
                // run user-defined filters, user-defined filters can override 
                // default filters.
                // NOTE: users must define css_import filter for production mode.
                if ( $collection->getFilters() ) {
                    $this->runUserDefinedFilters($collection);
                }
                // for stylesheets, before compress it, we should import the css contents
                elseif ( $collection->isStylesheet && $collection->filetype === Collection::FILETYPE_CSS ) {
                    // css import filter implies css rewrite
                    $import = new Filter\CssImportFilter;
                    $import->filter( $collection );
                } else {
                    $collection->runDefaultFilters();
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                if ( $collection->getFilters() ) {
                    $this->runUserDefinedFilters($collection);
                } else {
                    $collection->runDefaultFilters();
                }
            }
            if ( $collection->isJavascript || $collection->isCoffeescript ) {
                $out['js'] .= $collection->getContent();
            } elseif ( $collection->isStylesheet ) {
                $out['css'] .= $collection->getContent();
            }
        }
        return $out;
    }


    public function runDefaultCompressors($collection)
    {
        if ( $this->defaultJsCompressor 
            && ($collection->isJavascript || $collection->isCoffeescript) ) 
        {
            if ( $com = $this->getCompressor($this->defaultJsCompressor) ) {
                $com->compress($collection);
            }
        } elseif ( $collection->isStylesheet && $this->defaultCssCompressor ) {
            if ( $com = $this->getCompressor($this->defaultCssCompressor) ) {
                $com->compress($collection);
            }
        }
    }

    /**
     * Run compressors at the end
     *
     */
    public function runCollectionCompressors($collection)
    {
        // if custom compresor is not define, use default compressors
        if ( empty($collection->compressors) ) {
            $this->runDefaultCompressors($collection);
        } else {
            if ( $collection->hasCompressor('no') ) {
                return;
            }
            foreach( $collection->compressors as $n ) {
                if ( $compressor = $this->getCompressor( $n ) ) {
                    $compressor->compress($collection);
                } else {
                    throw new AssetCompilerException("compressor $n not found.");
                }
            }
        }
    }
}

