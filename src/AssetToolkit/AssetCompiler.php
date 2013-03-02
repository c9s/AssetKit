<?php
namespace AssetToolkit;
use Exception;
use AssetToolkit\FileUtil;

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
     * @var string Cache namespace
     */
    public $namespace;

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


    public function __construct($config,$loader)
    {
        $this->config = $config;
        $this->loader = $loader;
        $this->namespace = $this->config->getRoot();
    }


    public function setNamespace($ns)
    {
        $this->namespace = $ns;
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
        $this->addCompressor('jsmin', '\AssetToolkit\Compressor\JsMinCompressor');
        $this->addCompressor('cssmin', '\AssetToolkit\Compressor\CssMinCompressor');

        $this->addCompressor('yui_css', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetToolkit\Compressor\Yui\CssCompressor($bin);
        });

        $this->addCompressor('yui_js', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetToolkit\Compressor\Yui\JsCompressor($bin);
        });
    }

    public function registerDefaultFilters()
    {
        $this->addFilter( 'coffeescript','\AssetToolkit\Filter\CoffeeScriptFilter');
        $this->addFilter( 'css_import', '\AssetToolkit\Filter\CssImportFilter');
        $this->addFilter( 'sass', '\AssetToolkit\Filter\SassFilter');
        $this->addFilter( 'scss', '\AssetToolkit\Filter\ScssFilter');
        $this->addFilter( 'css_rewrite', '\AssetToolkit\Filter\CssRewriteFilter');
    }




    /**
     * Simply run filters through these assets.
     *
     */
    public function compileAssetsForDevelopment($assets)
    {
        $assets = (array)$assets;
        $out = array();

        $root = $this->config->getRoot();
        $baseDir = $this->config->getBaseDir(true);
        $baseUrl = $this->config->getBaseUrl();
        foreach( $assets as $asset ) {
            $assetBaseUrl = $baseUrl . '/' . $asset->name;
            foreach( $asset->getCollections() as $c ) {

                $type = null;
                if( $c->isCoffeescript || $c->isJavascript ) {
                    $type = 'javascript';
                } elseif( $c->isStylesheet ) {
                    $type = 'stylesheet';
                } else {
                    // skip non-filetype collections
                    continue;
                }

                // for collections has filters, 
                // pipe content through these filters.
                $filtered = false;
                if( $filters = $c->getFilters() ) {
                    $this->runCollectionFilters($c);
                    $filtered = true;
                } elseif( $c->isCoffeescript ) { // default filters
                    $coffee = new Filter\CoffeeScriptFilter;
                    $coffee->filter( $c );
                    $filtered = true;
                } elseif( $c->filetype == Collection::FILETYPE_SASS ) {
                    $sass = new Filter\SassFilter;
                    $sass->filter( $c );
                    $filtered = true;
                } elseif( $c->filetype == Collection::FILETYPE_SCSS ) {
                    $scss = new Filter\ScssFilter;
                    $scss->filter( $c );
                    $filtered = true;
                }

                // for coffee-script we need to pass the coffee-script to compiler
                // and get the javascript from the output, we can simply render the 
                // content in the pipe.
                if( $filtered ) {
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
    public function compile($asset) 
    {
        $cacheKey = $this->namespace . ':' . $asset->name;
        $cache = apc_fetch($cacheKey);

        // cache validation
        if( $cache ) {
            if( ! $this->productionFstatCheck ) {
                return $cache;
            } else {
                $upToDate = true;
                if( $mtime = @$cache['mtime'] ) {
                    if( $asset->isOutOfDate($mtime) ) {
                        $upToDate = false;
                        break;
                    }
                }
                if($outOfDate)
                    return $cache;
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

        if($out['js']) {
            $out['js_file'] = $jsFile;
            $out['js_url'] = $jsUrl;
            $this->writeFile( $jsFile, $out['js'] );
        }
        if($out['css']) {
            $out['css_file'] = $cssFile;
            $out['css_url'] = $cssUrl;
            $this->writeFile( $cssFile , $out['css'] );
        }
        apc_store($cacheKey, $out);
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
    public function compileAssetsForProduction($target, $assets, $force = false)
    {
        $cacheKey = $this->namespace . ':' . $target;
        $cache = apc_fetch($cacheKey);

        // cache validation
        if( $cache && ! $force ) {
            if( ! $this->productionFstatCheck ) {
                return $cache;
            } else {
                $upToDate = true;
                if( $mtime = @$cache['mtime'] ) {
                    foreach( $assets as $asset ) {
                        if( $asset->isOutOfDate($mtime) ) {
                            $upToDate = false;
                            break;
                        }
                    }
                }
                if($outOfDate)
                    return $cache;
            }
        }

        $manifests = array();
        foreach( $assets as $asset ) {
            $manifests[] = $this->compile($asset);
        }
        $contents = array(
            'js' => '',
            'css' => '',
        );

        // concat results
        foreach( $manifests as $m ) {
            if(isset($m['js_file']))
                $contents['js'] .= file_get_contents($m['js_file']);
            if(isset($m['css_file']))
                $contents['css'] .= file_get_contents($m['css_file']);
        }

        $compiledDir = $this->prepareCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $outfiles = array();

        // write minified results to file
        $outfiles['css_md5'] = md5($contents['css']);
        $outfiles['js_md5'] = md5($contents['js']);
        $outfiles['css_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['css_md5'] . '.min.css';
        $outfiles['js_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['js_md5'] . '.min.js';
        $outfiles['css_url'] = "$compiledUrl/$target-" . $outfiles['css_md5'] . '.min.css';
        $outfiles['js_url']  = "$compiledUrl/$target-" . $outfiles['js_md5']  . '.min.js';
        $outfiles['mtime']   = time();


        // write minified file
        $this->writeFile( $outfiles['js_file'], $contents['js'] );
        $this->writeFile( $outfiles['css_file'], $contents['css'] );
        apc_store($cacheKey, $outfiles);
        return $outfiles;
    }


    public function clean($m)
    {
        foreach( array('css_file','js_file')  as $k ) {
            if( $m[$k] ) {
                file_exists($m[$k]) && unlink($m[$k]);
            }   
        }
    }

    public function prepareCompiledDir()
    {
        $compiledDir = $this->config->getCompiledDir();
        if( ! file_exists($compiledDir) ) {
            mkdir($compiledDir, 0755, true);
        }
        if( ! is_writable($compiledDir) ) {
            throw new Exception("AssetCompiler: The $compiledDir is not writable.");
        }
        return $compiledDir;
    }


    public function writeFile($path,$content) 
    {
        if( false === file_put_contents($path, $content) ) {
            throw new Exception("AssetCompiler: can not write $path");
        }
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
        } elseif( class_exists($cb,true) ) {
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

        // check compressor builder
        if( ! isset($this->_compressors[$name]) )
            return;

        $cb = $this->_compressors[ $name ];

        if( is_string($cb) ) {
            if( class_exists($cb,true) ) {
                return $this->compressors[ $name ] = new $cb;
            } else {
                throw new Exception("$cb class not found.");
            }
        } else if( is_callable($cb) ) {
            return $this->compressors[ $name ] = call_user_func($cb);
        } else {
            throw new Exception("Unsupported compressor builder");
        }
    }

    public function getCompressors()
    {
        $self = $this;
        return array_map(function($n) use($self) { 
            return $self->getCompressor($n);
             }, $this->_compressors);
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
            if( ! $collection->isJavascript && ! $collection->isStylesheet )
                continue;

            if( $lastm = $collection->getLastModifiedTime() ) {
                if( $lastm > $out['mtime'] ) {
                    $out['mtime'] = $lastm;
                }
            }

            // if we are in development mode, we don't need to compress them all,
            // we just filter them
            if( $this->enableCompressor ) 
            {
                // for stylesheets, before compress it, we should import the css contents
                if( $collection->isStylesheet ) {
                    // import filter implies css rewrite
                    $import = new Filter\CssImportFilter;
                    $import->filter( $collection );
                } elseif( $collection->isCoffeescript ) {
                    $coffee = new Filter\CoffeeScriptFilter;
                    $coffee->filter( $collection );
                }
                if( $collection->getFilters() ) {
                    $this->runCollectionFilters( $collection );
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                $this->runCollectionFilters( $collection );
            }

            if( $collection->isJavascript ) {
                $out['js'] .= $collection->getContent();
            } elseif( $collection->isStylesheet ) {
                $out['css'] .= $collection->getContent();
            }
        }
        return $out;
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
            } else {
                throw new Exception("filter $n not found.");
            }
        }
    }

    /**
     * Run compressors at the end
     *
     *
     */
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
        } else {
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
}

