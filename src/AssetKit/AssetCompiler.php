<?php
namespace AssetKit;
use AssetKit\FileUtil;
use AssetKit\AssetUrlBuilder;
use AssetKit\Collection;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;

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
     * @var array filter builders
     */
    protected $_filters = array();

    /**
     * @var array compressor builders
     */
    protected $_compressors = array();

    public $defaultJsCompressor = 'jsmin';

    public $defaultCssCompressor = 'cssmin';

    public $autoAddUnknownTarget = false;

    public function __construct(AssetConfig $config, AssetLoader $loader)
    {
        $this->config = $config;
        $this->loader = $loader;
        $this->urlBuilder = new AssetUrlBuilder($config);
    }

    public function setConfig(AssetConfig $config)
    {
        $this->config = $config;
    }


    public function setLoader(AssetLoader $loader)
    {
        $this->loader = $loader;
    }


    /**
     * Register the default compressors that are supported from AssetKit.
     */
    public function registerDefaultCompressors()
    {
        // $this->registerCompressor('jsmin', '\AssetKit\Compressor\JsMinExtCompressor');
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
     * Simply run filters through these assets.
     *
     * @param AssetKit\Assets[] asset objects
     */
    public function compileAssets(array $assets, $target = null)
    {
        $assetNames = array();
        $out = array();

        $root = $this->config->getRoot();
        $baseDir = $this->config->getBaseDir(true);
        foreach( $assets as $asset ) {
            $assetNames[] = $asset->name;
            $assetBaseUrl = $this->urlBuilder->buildBaseUrl($asset);

            foreach( $asset->getCollections() as $c ) {

                $type = null;
                if ( $c->isScript ) {
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
        if ( $this->autoAddUnknownTarget && $target ) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->loader->addTarget($target, $assetNames);
            $this->loader->saveEntries();
        }

        return $out;
    }




    /**
     * Clean up compiled files 
     *
     * @param array $meta
     */
    public function clean(array $meta)
    {
        foreach( array('css_file','js_file') as $key ) {
            if ( $meta[$key] ) {
                futil_unlink_if_exists( $meta[$key] );
            }
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
        $assetBaseUrl = $this->urlBuilder->buildBaseUrl($asset);

        if ($collection->filetype === Collection::FileTypeCoffee) {
            $coffee = new CoffeeScriptFilter($this->config);
            $coffee->filter( $collection );
            return true;
        } elseif ( $collection->filetype === Collection::FileTypeSass ) {
            $sass = new SassFilter($this->config, $assetBaseUrl);
            $sass->filter($collection);
            return true;
        } elseif ( $collection->filetype === Collection::FileTypeScss ) {
            $scss = new ScssFilter($this->config, $assetBaseUrl);
            $scss->filter( $collection );
            return true;
        }
        return false;
    }



}

