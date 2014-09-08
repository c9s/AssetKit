<?php
namespace AssetKit;
use ConfigKit\ConfigCompiler;
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

class ProductionAssetCompiler extends AssetCompiler
{

    /**
     * @var string checksum algorithm, used for squashed css/js content.
     */
    public $checksumAlgo = 'md5';


    public $prepareCompiledDir = true;

    public $chmodCompiledDir = true;

    public $defaultCompiledDirMod = 0777;

    public function __construct(AssetConfig $config, AssetLoader $loader) {
        parent::__construct($config, $loader);

        if ($this->prepareCompiledDir) {
            $this->prepareCompiledDir();
        }
    }


    /**
     * Set checksum algorithm for generating content checksum
     *
     * @param string $algo
     */
    public function setChecksumAlgorithm($algo)
    {
        $this->checksumAlgo = $algo;
    }




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
    public $checkFstat = false;

    /**
     * @var boolean serialize compilation info into a file.
     */
    public $writeMetaFile = false;


    public function enableFstatCheck()
    {
        $this->checkFstat = true;
    }

    public function fstatCheckEnabled() {
        return $this->checkFstat;
    }


    public function assetsAreOutOfDate($assets, $mtime) {
        foreach( $assets as $asset ) {
            if ( $asset->isOutOfDate($mtime) ) {
                return true;
            }
        }
        return false;
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
     * @param Asset[] $assets
     * @param string $target target name
     * @param boolean $force force compilation
     */
    public function compileAssets($assets, $target = '', $force = false)
    {
        $targetDefined = $target ? true : false;
        if ( $target ) {
            $cacheKey = $this->config->getNamespace() . ':target:' . $target;
        } else {
            $cacheKey = $this->config->getNamespace() . ':' . $this->_generateCacheKeyFromAssets($assets);
            $target = $this->config->getDefaultTarget();
        }


        if ( $cache = $this->config->getCache() ) {
            $cached = $cache->get($cacheKey);

            // cache validation
            if ($cached && ! $force) {
                if (! $this->checkFstat || ! isset($cached['mtime'])) {
                    return $cached;
                }
                if (! $this->assetsAreOutOfDate($assets, $cached['mtime'])) {
                    return $cached;
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
        if ( $targetDefined ) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->config->addTarget($target, $assetNames);

            // the config filename is defined.
            if ($this->config->getConfigFile() ) {
                $this->config->save();
            }
        }

        $compiledDir = $this->config->getCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $outfiles = array();

        // write minified results to file
        if ($contents['js']) {
            $outfiles['js_checksum'] = hash($this->checksumAlgo, $contents['js']);
            $outfiles['js_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['js_checksum'] . '.min.js';
            $outfiles['js_url']  = "$compiledUrl/$target-" . $outfiles['js_checksum']  . '.min.js';
            file_put_contents($outfiles['js_file'], $contents['js'] );
        }

        if ($contents['css']) {
            $outfiles['css_checksum'] = hash($this->checksumAlgo, $contents['css']);
            $outfiles['css_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['css_checksum'] . '.min.css';
            $outfiles['css_url'] = "$compiledUrl/$target-" . $outfiles['css_checksum'] . '.min.css';
            file_put_contents($outfiles['css_file'], $contents['css'] );
        }


        $outfiles['assets']  = $assetNames;
        $outfiles['mtime']   = time();
        $outfiles['cache_key'] = $cacheKey;
        $outfiles['target'] = $target;

        if ($this->writeMetaFile) {
            $outfiles['metafile'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '.meta.php';
            ConfigCompiler::write($outfiles['metafile'], $outfiles);
        }

        // include entries
        $result = array($outfiles);
        if ( $cache = $this->config->getCache() ) {
            $cache->set($cacheKey, $result);
        }
        return $result;
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
            $cached = $this->config->cache->get($cacheKey);

            // cache validation
            if ( $cached ) {
                if ( ! $this->checkFstat || ! isset($cached['mtime']) ) {
                    return $cached;
                }
                if ( ! $asset->isOutOfDate($cached['mtime']) ) {
                    return $cached;
                }
            }
        }

        $out = $this->squash($asset);
        $prefixName = $asset->name . '.min';

        $compiledDir = $this->config->getCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();

        $jsFile = $compiledDir . DIRECTORY_SEPARATOR . $prefixName . '.js';
        $cssFile = $compiledDir . DIRECTORY_SEPARATOR . $prefixName . '.css';
        $jsUrl = $compiledUrl . "/$prefixName.js";
        $cssUrl = $compiledUrl . "/$prefixName.css";

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

    public function prepareCompiledDir()
    {
        $compiledDir = $this->config->getCompiledDir();

        if (! file_exists($compiledDir)) {
            mkdir($compiledDir,$this->defaultCompiledDirMod, true);
        }

        if (!is_dir($compiledDir)) {
            throw new RuntimeException("The $compiledDir is not a directory.");
        }

        if (!is_writable($compiledDir)) {
            throw new UnwritableFileException("The $compiledDir is not writable for asset compilation.");
        }

        if ($this->chmodCompiledDir) {
            chmod($compiledDir,$this->defaultCompiledDirMod);
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
        $out = array(
            'js' => '',
            'css' => '',
            'mtime' => 0,
        );
        $collections = $asset->getCollections();
        $assetBaseUrl = $this->urlBuilder->buildBaseUrl($asset);
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


}




