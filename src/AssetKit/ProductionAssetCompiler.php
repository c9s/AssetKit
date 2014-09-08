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


    /**
     * @var boolean Create compiled directory if it does not exist.
     */
    public $autoPrepareCompiledDir = true;

    /**
     * @var boolean Change the permission mode of compiled directory everytime 
     *              when preparing compiled directory. this is useful for debugging.
     */
    public $chmodCompiledDir = true;

    public $defaultCompiledDirMod = 0777;

    public function __construct(AssetConfig $config, AssetLoader $loader) {
        parent::__construct($config, $loader);

        if ($this->autoPrepareCompiledDir) {
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
     * Cache mode: Use simple PHP config 
     */
    const CACHE_METAFILE = 1;

    /**
     * Cache mode: Use UniversalCache\UniversalCache to caceh the compilation result.
     */
    const CACHE_UNIVERSAL = 2;

    /**
     * @var integer define the cache type
     */
    public $cacheType = self::CACHE_METAFILE;

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
        if (! $target ) {
            $target = $this->generateTargetNameFromAssets($assets);
        }

        $compiledDir = $this->config->getCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $metaFile = $compiledDir . DIRECTORY_SEPARATOR . '.' . $target . '.meta.php';
        $cacheKey = $this->config->getNamespace() . ':target:' . $target;

        if (!$force) {
            $cached = NULL;
            if ($this->cacheType === self::CACHE_METAFILE && file_exists($metaFile)) {
                $cached = require $metaFile;
            } else if ($this->cacheType === self::CACHE_UNIVERSAL) { 
                if ($cache = $this->config->getCache()) {
                    $cached = $cache->get($cacheKey);
                }
            }

            if ($cached) {
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

        // register target (assets) to the config, if it's not defaultTarget and the config file name is defined.
        if ( $targetDefined && $this->config->getConfigFile()) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->config->addTarget($target, $assetNames);
            $this->config->save();
        }

        $entry = array();

        // write minified results to file
        if ($contents['js']) {
            $entry['js_checksum'] = hash($this->checksumAlgo, $contents['js']);
            $filename = $target . '-' . $entry['js_checksum'] . '.min.js';
            $entry['js_file'] = $compiledDir . DIRECTORY_SEPARATOR . $filename;
            $entry['js_url']  = "$compiledUrl/" . $filename;
            if (false === file_put_contents($entry['js_file'], $contents['js'], LOCK_EX)) {
                throw new Exception("Can't write file '{$entry['js_file']}'");
            }
        }

        if ($contents['css']) {
            $entry['css_checksum'] = hash($this->checksumAlgo, $contents['css']);
            $filename = $target . '-' . $entry['css_checksum'] . '.min.css';
            $entry['css_file'] = $compiledDir . DIRECTORY_SEPARATOR . $filename;
            $entry['css_url'] = "$compiledUrl/" . $filename;
            if (false === file_put_contents($entry['css_file'], $contents['css'], LOCK_EX) ) {
                throw new Exception("Can't write file '{$entry['css_file']}'");
            }
        }


        $entry['assets']  = $assetNames;
        $entry['mtime']   = time();
        $entry['cache_key'] = $cacheKey;
        $entry['target'] = $target;
        $entry['metafile'] = $metaFile;

        // include entries
        $entries = array($entry);

        if ($this->cacheType === self::CACHE_UNIVERSAL) {
            if ( $cache = $this->config->getCache() ) {
                $cache->set($cacheKey, $entries);
            }
        }
        // always write the meta file
        ConfigCompiler::write($entry['metafile'], $entries);
        return $entries;
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
        $compiledDir = $this->config->getCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $metaFile = $compiledDir . DIRECTORY_SEPARATOR . ".asset-" . $asset->name . '.meta.php';

        if (! $force && file_exists($metaFile)) {
            $cached = require $metaFile;
            if ( ! $this->checkFstat || ! isset($cached['mtime']) ) {
                return $cached;
            }
            if ( ! $asset->isOutOfDate($cached['mtime']) ) {
                return $cached;
            }
        }

        $prefixName = $asset->name . '.min';
        $jsFile = $compiledDir . DIRECTORY_SEPARATOR . $prefixName . '.js';
        $cssFile = $compiledDir . DIRECTORY_SEPARATOR . $prefixName . '.css';
        $jsUrl = $compiledUrl . "/$prefixName.js";
        $cssUrl = $compiledUrl . "/$prefixName.css";

        /*
        $fp = fopen($jsFile, "w");
        if (flock($fp, LOCK_EX, $wouldBlock)) {  }
        */

        $out = $this->squash($asset);
        if ($out['js']) {
            $out['js_file'] = $jsFile;
            $out['js_url'] = $jsUrl;
            if (false === file_put_contents($jsFile, $out['js'], LOCK_EX)) {
                throw new Exception("Can't write file '$jsFile'");
            }
        }
        if ($out['css']) {
            $out['css_file'] = $cssFile;
            $out['css_url'] = $cssUrl;
            if (false === file_put_contents($cssFile , $out['css'], LOCK_EX)) {
                throw new Exception("Can't write file '$cssFile'");
            }
        }

        // store cache
        ConfigCompiler::write($metaFile, $out);
        return $out;
    }


    public function generateTargetNameFromAssets($assets)
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




