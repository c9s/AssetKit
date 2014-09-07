<?php
namespace AssetKit;
use AssetKit\FileUtil;
use AssetKit\AssetUrlBuilder;
use AssetKit\Collection;

// Filters
use AssetKit\Filter\SassFilter;
use AssetKit\Filter\ScssFilter;
use AssetKit\Filter\CoffeeScriptFilter;
use AssetKit\Filter\CssImportFilter;

// Compressors
use AssetKit\Compressor\Yui\JsCompressor as YuiJsCompressor;
use AssetKit\Compressor\Yui\CssCompressor as YuiCssCompressor;

// Exceptions
use AssetKit\Exception\UndefinedFilterException;
use AssetKit\Exception\UndefinedCompressorException;
use AssetKit\Exception\UnwritableFileException;
use Exception;
use RuntimeException;
use InvalidArgumentException;


class AssetCompilerException extends Exception {  }

class AssetCompiler
{

    /**
     * @var AssetKit\AssetConfig asset config object.
     */
    public $config;


    /**
     * @var AssetKit\AssetLoader asset loader object.
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

    public $defaultCompiledDirPermission = 0777;


    public function __construct($config,$loader)
    {
        $this->config = $config;
        $this->loader = $loader;
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
        $this->registerCompressor('jsmin', '\AssetKit\Compressor\JsMinCompressor');
        $this->registerCompressor('cssmin', '\AssetKit\Compressor\CssMinCompressor');
        $this->registerCompressor('uglifyjs', '\AssetKit\Compressor\UglifyCompressor');

        $this->registerCompressor('yui_css', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new YuiCssCompressor($bin);
        });

        $this->registerCompressor('yui_js', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new YuiJsCompressor($bin);
        });
    }

    public function registerDefaultFilters()
    {
        $this->registerFilter( 'coffeescript','\AssetKit\Filter\CoffeeScriptFilter');
        $this->registerFilter( 'css_import', '\AssetKit\Filter\CssImportFilter');
        $this->registerFilter( 'sass', '\AssetKit\Filter\SassFilter');
        $this->registerFilter( 'scss', '\AssetKit\Filter\ScssFilter');
        $this->registerFilter( 'css_rewrite', '\AssetKit\Filter\CssRewriteFilter');
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
     * @param AssetKit\Assets[] asset objects
     */
    public function compileAssets($assets, $target = null)
    {
        $assets = (array)$assets;
        $assetNames = array();
        $out = array();

        $urlBuilder = new AssetUrlBuilder($this->config);

        $root = $this->config->getRoot();
        $baseDir = $this->config->getBaseDir(true);

        foreach( $assets as $asset ) {
            $assetNames[] = $asset->name;
            $assetBaseUrl = $urlBuilder->buildBaseUrl($asset);

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
                    $filtered = $this->runDefaultFilters($asset, $c);
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
            file_put_contents( $jsFile, $out['js'] );
        }
        if ($out['css']) {
            $out['css_file'] = $cssFile;
            $out['css_url'] = $cssUrl;
            file_put_contents( $cssFile , $out['css'] );
        }

        if ( $this->config->cache ) {
            $this->config->cache->set($cacheKey, $out);
        }
        return $out;
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

        if (! file_exists($compiledDir)) {
            mkdir($compiledDir,$this->defaultCompiledDirPermission, true);
        }

        if (!is_dir($compiledDir)) {
            throw new RuntimeException("The $compiledDir is not a directory.");
        }

        if (!is_writable($compiledDir)) {
            throw new UnwritableFileException("The $compiledDir is not writable for asset compilation.");
        }
        chmod($compiledDir,$this->defaultCompiledDirPermission);
        return $compiledDir;
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
        if ( isset($this->filters[$name]) ) {
            return $this->filters[$name];
        }

        // check the factory closure
        if ( ! isset($this->_filters[$name]) ) {
            throw new UndefinedFilterException("$name filter is undefined.");
        }

        $cb = $this->_filters[ $name ];
        if ( is_callable($cb) ) {
            return $this->filters[ $name ] = call_user_func($cb);
        } elseif ( class_exists($cb,true) ) {
            return $this->filters[ $name ] = new $cb($this->config);
        }
        throw new InvalidArgumentException("Unsupported filter builder type.");
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
        if ( isset($this->compressors[$name]) ) {
            return $this->compressors[$name];
        }

        // check compressor builder
        if ( ! isset($this->_compressors[$name]) ) {
            throw new UndefinedCompressorException("$name compressor is undefined.");
        }

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
            throw new InvalidArgumentException("Unsupported compressor builder type");
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
     * Run user-defined filters to the file collection
     *
     * @param Collection the collection objecct
     */
    public function runUserDefinedFilters(Collection $collection)
    {
        if ( empty($this->filters) ) {
            return false;
        }
        if ( $this->hasFilter('no') ) {
            return false;
        }
        foreach( $this->filters as $n ) {
            $filter = $this->getFilter( $n );
            $filter->filter($collection);
            return true; // XXX: check this logic flow
        }
        return false;
    }


    /**
     * Run default filters, for coffee-script, sass, scss filetype,
     * these content must be filtered.
     *
     * @param Asset 
     * @param Collection
     *
     * @return bool returns true if filter is matched, returns false if there is no filter matched.
     */
    public function runDefaultFilters(Asset $asset, Collection $collection)
    {
        $urlBuilder = new AssetUrlBuilder($this->config);
        $assetBaseUrl = $urlBuilder->buildBaseUrl($asset);

        if ( $collection->isCoffeescript || $collection->filetype === Collection::FILETYPE_COFFEE ) {
            $coffee = new CoffeeScriptFilter($this->config);
            $coffee->filter( $collection );
            return true;
        } elseif ( $collection->filetype === Collection::FILETYPE_SASS ) {
            $sass = new SassFilter($this->config, $assetBaseUrl);
            $sass->filter($collection);
            return true;
        } elseif ( $collection->filetype === Collection::FILETYPE_SCSS ) {
            $scss = new ScssFilter($this->config, $assetBaseUrl);
            $scss->filter( $collection );
            return true;
        }
        return false;
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
        $out = array(
            'js' => '',
            'css' => '',
            'mtime' => 0,
        );
        $collections = $asset->getCollections();
        $urlBuilder = new AssetUrlBuilder($this->config);
        $assetBaseUrl = $urlBuilder->buildBaseUrl($asset);
        foreach( $collections as $collection ) {
            // skip unknown collection type
            if ( ! $collection->isJavascript && ! $collection->isStylesheet && ! $collection->isCoffeescript )
                continue;

            if ( $lastm = $collection->getLastModifiedTime() ) {
                if ( $lastm > $out['mtime'] ) {
                    $out['mtime'] = $lastm;
                }
            }

            // If we are in development mode, we don't need to compress them all,
            // we just filter them
            if ( $this->enableCompressor ) 
            {
                // Run user-defined filters, user-defined filters can override 
                // default filters.
                // NOTE: users must define css_import filter for production mode.
                if ( $collection->getFilters() ) {
                    $this->runUserDefinedFilters($collection);
                }
                // for stylesheets, before compress it, we should import the css contents
                elseif ( $collection->isStylesheet && $collection->filetype === Collection::FILETYPE_CSS ) {
                    // css import filter implies css rewrite
                    $import = new CssImportFilter($this->config, $assetBaseUrl);
                    $import->filter( $collection );
                } else {
                    $this->runDefaultFilters($asset, $collection);
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                if ( $collection->getFilters() ) {
                    $this->runUserDefinedFilters($collection);
                } else {
                    $this->runDefaultFilters($asset, $collection);
                }
            }

            // concat js and css
            if ( $collection->isJavascript || $collection->isCoffeescript ) {
                $out['js'] .= ";" . $collection->getContent() . "\n";
            } elseif ( $collection->isStylesheet ) {
                $out['css'] .= $collection->getContent() . "\n";
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
                $compressor = $this->getCompressor( $n );
                $compressor->compress($collection);
            }
        }
    }
}

