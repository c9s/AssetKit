<?php
namespace AssetToolkit;
use ZipArchive;
use Exception;
use SerializerKit;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AssetToolkit\FileUtil;
use AssetToolkit\FileUtils;
use ConfigKit\ConfigCompiler;


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
     *
     *     "assets/jquery"
     *     "bundles/ResourceA/assets/underscore"
     *     "bundles/ResourceA/assets/font-awesome"
     *
     */
    public $sourceDir;


    public $baseDir;



    /**
     * @var string manifest file path, we assume that the manifest file should be PHP format.
     *
     *     "bundles/ResourceA/assets/font-awesome/manifest.yml"
     */
    public $manifestFile;


    /**
     * @var AssetToolkit\Collection[]
     */
    public $collections = array();


    public function __construct() { }


    /**
     *
     *
     * @param string $manifestYamlFile related YAML manifest file path, which 
     *          should be absolute path.
     */
    public function loadFromManifestFile($manifestYamlFile)
    {
        $data = ConfigCompiler::load($manifestYamlFile);
        $this->manifestFile = $manifestYamlFile;
        $this->sourceDir    = dirname($manifestYamlFile);
        $this->name         = basename($this->sourceDir);
        $this->loadFromArray($data);
    }


    public function loadFromArray($config)
    {
        $this->stash = $config;
        // load assets
        if( isset($this->stash['collections']) ) {
            // create collection objects
            $this->collections = $this->loadCollections($this->stash['collections']);
        } else {
            throw new Exception("the 'collections' is not defined in {$this->name}");
        }
    }


    /**
     * This method create collection objects based on the config from manifest file,
     *
     * File paths will be expanded.
     *
     * Thie method copies class members to to the file collection
     *
     * TODO: Save the absolute path in our cache.
     * TODO: Save the collection object in the asset config, so we may use APC to cache the objects.
     *       To save the collection objects in our APC, the objects must not depend on the config/loader object.
     *       
     */
    public function loadCollections( $collectionStash )
    {
        $sourceDir = $this->sourceDir;
        $collections = array();

        foreach( $collectionStash as $stash ) {
            $collection = new Collection;
            $files = array();

            // for normal static files
            if( isset($stash['files']) ) {
                $files            = $stash['files'];
                $collection->filetype = Collection::FILETYPE_FILE;
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
            } elseif (isset($stash['coffee'])) {
                $files                      = $stash['coffee'];
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

            if (isset($stash['attrs']) ) {
                $collection->attributes = $stash['attrs'];
            }

            $expandedFiles = array();
            foreach( $files as $p ) {

                // found a glob pattern
                if( strpos($p,'*') !== false )
                {
                    $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);

                    // should be unique
                    $expandedFiles = array_unique( array_merge( $expandedFiles , $expanded ) );

                } elseif( is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p ) ) {
                    $expanded = FileUtil::expand_dir_recursively( $sourceDir . DIRECTORY_SEPARATOR . $p );

                    // We remove the base dir becase we need to build the 
                    // asset urls
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
            $collection->sourceDir = $this->getSourceDir();
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
            'stash'       => $this->stash,
            'manifest'    => $this->manifestFile,
            'source_dir'  => $this->sourceDir,
            // 'collections' => $this->collections,
            'name'        => $this->name,
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
     * Get the asset source directory
     *
     * @param bool $absolute
     */
    public function getSourceDir()
    {
        return $this->sourceDir;
    }


    /**
     * Check if collection files are out of date.
     */
    public function isOutOfDate($fromTime)
    {
        $collections = $this->getCollections();
        foreach( $collections as $c ) {
            // if the collectino is newer than from time.
            if ( $c->isOutOfDate($fromTime) ) {
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
            $paths = $collection->getSourcePaths();
            foreach( $paths as $path ) {
                if ( ! file_exists($path) ) {
                    return false;
                }
            }
        }
        return true;
    }

    public function __set_state($array) {
        // TODO: implement this
    }

}



