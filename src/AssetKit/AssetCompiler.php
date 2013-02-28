<?php
namespace AssetKit;
use Exception;

class AssetCompiler
{
    const PRODUCTION = 1;
    const DEVELOPMENT = 2;


    /**
     * Can be AssetCompiler::PRODUCTION or AssetCompiler::DEVELOPMENT
     *
     * $compiler->setEnvironment( AssetCompiler::PRODUCTION );
     * $compiler->setEnvironment( AssetCompiler::DEVELOPMENT );
     */
    public $environment = self::DEVELOPMENT;


    public $enableCompressor = true;

    /**
     * @var array cached filters
     */
    protected $filters = array();


    /**
     * @var array cached compressors
     */
    protected $compressors = array();

    // filter builder
    protected $_filters = array();

    // compressor builder
    protected $_compressors = array();


    public function setEnvironment($env)
    {
        $this->environment = $env;
    }


    public function registerDefaultCompressors()
    {
        $this->addCompressor('jsmin', '\AssetKit\Compressor\JsMinCompressor');
        $this->addCompressor('cssmin', '\AssetKit\Compressor\CssMinCompressor');

        $this->addCompressor('yui_css', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetKit\Compressor\Yui\CssCompressor($bin);
        });

        $this->addCompressor('yui_js', function() {
            $bin = getenv('YUI_COMPRESSOR_BIN');
            return new \AssetKit\Compressor\Yui\JsCompressor($bin);
        });
    }

    public function registerDefaultFilters()
    {
        $this->addFilter( 'coffeescript','\AssetKit\Filter\CoffeeScriptFilter');
        $this->addFilter( 'css_import', '\AssetKit\Filter\CssImportFilter');
        $this->addFilter( 'sass', '\AssetKit\Filter\SassFilter');
        $this->addFilter( 'scss', '\AssetKit\Filter\ScssFilter');
        $this->addFilter( 'css_rewrite', '\AssetKit\Filter\CssRewriteFilter');
    }



    /**
     * Method for compiling one asset
     *
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
     */
    public function compile($asset) 
    {
        $data = $this->squash($asset);

        // get the absolute path of install dir.
        $installDir = $asset->getInstallDir(true);
        $baseUrl    = $asset->getBaseUrl();
        $name = $asset->name;

        if( $this->environment === self::PRODUCTION ) {
            $name = $name . '.min';
        }

        $jsFile = $installDir . DIRECTORY_SEPARATOR . $name . '.js';
        $cssFile = $installDir . DIRECTORY_SEPARATOR . $name . '.css';

        $jsUrl = $baseUrl . "/$name.js";
        $cssUrl = $baseUrl . "/$name.css";

        if($data['js'])
            file_put_contents( $jsFile, $data['js'] );

        if($data['css'])
            file_put_contents( $cssFile, $data['css'] );


        return array(
            'js'      => array($jsFile),
            'css'     => array($cssFile),
            'js_url'  => array($jsUrl),
            'css_url' => array($cssUrl),
        );
    }



    /**
     * Compile multiple assets.
     */
    public function compileAssets($assets) 
    {

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
     * @param  AssetKit\Asset $asset
     * @return array [ css: string, js: string ]
     */
    public function squash($asset)
    {
        $js = '';
        $css = '';
        $collections = $asset->getCollections();

        foreach( $collections as $collection ) {

            // skip unknown types
            if( ! $collection->isJavascript && ! $collection->isStylesheet )
                continue;

            // if we are in development mode, we don't need to compress them all,
            // we just filter them
            if( $this->environment === self::PRODUCTION 
                    && $this->enableCompressor ) 
            {
                // for stylesheets, before compress it, we should import the css contents
                if( $collection->isStylesheet ) {
                    // import filter implies css rewrite
                    $import = new Filter\CssImportFilter;
                    $import->filter( $collection );
                }
                elseif( $collection->isCoffeescript ) {
                    $coffee = new Filter\CoffeeScriptFilter;
                    $coffee->filter( $collection );
                }

                if( $collection->getFilters() ) {
                    $this->runCollectionFilters( $collection );
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                // for development mode, simply run filters
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
}

