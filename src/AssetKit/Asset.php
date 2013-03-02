<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;
use AssetKit\FileUtils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AssetKit\FileUtil;


/**
 * Asset class
 *
 * Asset object can be created from a manifest file.
 * Or can just be created with no arguments.
 *
 *
 *    assets/jquery          <-- source dir
 *    public/assets/jquery   <-- install dir
 *    public                 <-- asset base dir
 *    
 *    /assets                <-- asset base url
 *    /assets/jquery         <-- asset url
 *
 * Manifest file
 *
 *    assets/jquery/manifest.yml  <-- manifest
 *    public/assets/jquery/manifest.yml <-- public manifest
 * 
 * Compiled assets
 *
 *    assets/jquery/js/*.js       <-- js source
 *    assets/jquery/js/*.cs       <-- cs source
 *    assets/jquery/css/*.css     <-- css source
 *    assets/jquery/css/*.sass    <-- sass source
 *
 *    public/assets/jquery/jquery.min.css
 *    public/assets/jquery/jquery.min.js
 *
 */
class Asset
{
    /**
     * @var string the asset name
     */
    public $name;


    /**
     * @var array config stash.
     */
    public $stash;

    /**
     * @var string asset dir (related path, relate to config file) 
     */
    public $sourceDir;


    /**
     * @var string manifest file path, we assume that the manifest file should be PHP format.
     */
    public $manifestFile;


    /**
     * @var AssetKit\AssetConfig
     */
    public $config;


    /**
     * @var AssetKit\Collection[]
     */
    public $collections = array();


    public function __construct($config) 
    {
        $this->config = $config;
    }


    public function loadFromManifestFile($manifestFile, $format = 0)
    {
        $config = null;
        if( $format ) {
            $config = Data::decode_file($manifestFile, $format);
        } else {
            $config = Data::detect_format_and_decode( $manifestFile );
        }
        $this->manifestFile = $manifestFile;
        $this->sourceDir    = dirname($manifestFile);
        $this->name         = basename($this->sourceDir);
        $this->loadFromArray($config);
    }


    public function loadFromArray($config)
    {
        $this->stash = $config;
        // load assets
        if( isset($this->stash['collections']) ) {
            // create collection objects
            $this->collections = $this->create_collections($this->stash['collections']);
        } else {
            throw new Exception("the 'collections' is not defined in {$this->name}");
        }
    }


    /**
     * simply copy class members to to the file collection
     */
    public function create_collections( $collectionStash )
    {
        $sourceDir = $this->sourceDir;
        $collections = array();

        foreach( $collectionStash as $stash ) {
            $collection = new Collection;
            $files = array();

            
            // for normal static files
            if( isset($stash['files']) ) {
                $files            = $stash['files'];
                $collection->type = Collection::FILETYPE_FILE;
            } elseif (isset($stash['js'])) {
                $files                    = $stash['js'];
                $collection->filetype     = Collection::FILETYPE_JS;
                $collection->isJavascript = true;
            } elseif (isset($stash['javascript'])) {
                $files                    = $stash['javascript'];
                $collection->filetype     = Collection::FILETYPE_JS;
                $collection->isJavascript = true;
            } elseif (isset($stash['coffeescript'])) {
                $files                      = $stash['coffeescript'];
                $collection->filetype       = Collection::FILETYPE_COFFEE;
                $collection->isCoffeescript = true;
            } elseif (isset($stash['css'])) {
                $files                    = $stash['css'];
                $collection->filetype     = Collection::FILETYPE_CSS;
                $collection->isStylesheet = true;
            } elseif (isset($stash['sass'])) {
                $files                    = $stash['sass'];
                $collection->filetype     = Collection::FILETYPE_SASS;
                $collection->isStylesheet = true;
            } elseif (isset($stash['scss'])) {
                $files                    = $stash['scss'];
                $collection->filetype     = Collection::FILETYPE_SCSS;
                $collection->isStylesheet = true;
            } elseif (isset($stash['stylesheet']) ) {
                $files                    = $stash['stylesheet'];
                $collection->filetype     = Collection::FILETYPE_CSS;
                $collection->isStylesheet = true;
            } else {
                var_dump( $this ); 
                var_dump( $stash );
                throw new Exception('Unknown collection file type.');
            }

            $expandedFiles = array();
            foreach( $files as $p ) {

                // found glob pattern
                if( strpos($p,'*') !== false )
                {
                    $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);

                    // should be unique
                    $expandedFiles = array_unique( array_merge( $expandedFiles , $expanded ) );

                } elseif( is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p ) ) {

                    $expanded = FileUtil::expand_dir_recursively( $sourceDir . DIRECTORY_SEPARATOR . $p );
                    $expanded = FileUtil::remove_basedir_from_paths($expanded , $sourceDir);

                    $expandedFiles = array_unique(array_merge( $expandedFiles , $expanded ));

                } else {
                    $expandedFiles[] = $p;
                }
            }

            if( isset($stash['filters']) ) {
                $collection->filters = $stash['filters'];
            }
            if( isset($stash['compressors']) ) {
                $collection->compressors = $stash['compressors'];
            }
            $collection->files = $expandedFiles;
            $collection->asset = $this;
            $collections[] = $collection;
        }
        return $collections;
    }

    public function getCollections()
    {
        return $this->collections;
    }

    public function export()
    {
        // we should also save installed_dir
        // installed_dir = public dir + source dir
        return array(
            'stash'      => $this->stash,
            'manifest'   => $this->manifestFile,
            'source_dir' => $this->sourceDir,
            'name'       => $this->name,
        );
    }


    /**
     * @return string Asset name
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Get installation dir (the target directory of public)
     */
    public function getInstallDir($absolute = false)
    {
        return $this->config->getBaseDir(true) . DIRECTORY_SEPARATOR . $this->name;
    }

    /**
     * Get the asset source directory
     *
     * @param bool $absolute
     */
    public function getSourceDir($absolute = false)
    {
        return $absolute
            ? $this->config->getRoot() . DIRECTORY_SEPARATOR . $this->sourceDir
            : $this->sourceDir ;
    }


    public function getBaseUrl() 
    {
        return $this->config->getBaseUrl() . '/' . $this->name;
    }

    public function isOutOfDate($mtimeB)
    {
        $collections = $this->getCollections();
        foreach( $collections as $c ) {
            // if it's newer
            if( $c->getLastModifiedTime() > $mtimeB ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check source file existence.
     *
     * @return bool
     */
    public function hasSourceFiles()
    {
        foreach( $this->collections as $collection ) {
            $paths = $collection->getSourcePaths(true);
            foreach( $paths as $path ) {
                if( ! file_exists($path) )
                    return false;
            }
        }
        return true;
    }


}



