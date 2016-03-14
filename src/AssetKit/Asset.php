<?php
namespace AssetKit;
use ZipArchive;
use SerializerKit;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AssetKit\FileUtil;
use AssetKit\FileUtils;
use ConfigKit\ConfigCompiler;
use ArrayAccess;


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
class Asset implements ArrayAccess
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

    public $manifestCacheFile;


    /**
     * @var AssetKit\Collection[]
     */
    public $collections = array();


    /**
     * We keep the constructor empty because sometimes we need to create an 
     * asset object and setup later.
     */
    public function __construct() { }


    /**
     *
     *
     * @param string $manifestYamlFile related YAML manifest file path, which 
     *          should be absolute path.
     *
     * @param string $name Asset name. If any
     *
     * @param boolean $force force compile manifest file even there is a cached file.
     */
    public function loadManifestFile($manifestYamlFile, $name = NULL, $force = false)
    {
        $this->name = $name ?: basename($this->sourceDir);
        $this->setManifestFile($manifestYamlFile);
        $this->compileManifestFile($force);
        // $this->loadFromArray($stash);
    }

    static public function createFromManifestFile($manifestYamlFile, $name = NULL, $force = false) 
    {
        $asset = new Asset;
        $asset->loadManifestFile($manifestYamlFile, $name, $force);
        return $asset;
    }


    public function setSourceDir($dir) {
        $this->sourceDir = $dir;
    }


    /**
     *
     * @param string $manifestYamlFile
     */
    public function setManifestFile($manifestYamlFile) {
        $this->manifestFile = $manifestYamlFile;
        $this->sourceDir    = dirname($manifestYamlFile);
        $this->manifestCacheFile = ConfigCompiler::compiled_filename($manifestYamlFile);
    }


    /**
     * This method should only "compile" the YAML file and load the data into the "stash" property.
     *
     * @param bool $force
     */
    protected function compileManifestFile($force = false) {
        if ($force || ConfigCompiler::test($this->manifestFile, $this->manifestCacheFile)) {
            $stash = ConfigCompiler::parse($this->manifestFile);

            // expand file list
            if (isset($stash['collections'])) {
                foreach($stash['collections'] as & $cStash) {
                    $key = $this->_getFileListKey($cStash);
                    if (!$key) {
                        throw new Exception("{$this->manifestFile}: type key undefined.");
                    }
                    $cStash[$key] = $this->expandFileList($this->sourceDir, $cStash[$key]);
                }
            }

            // write config back
            ConfigCompiler::write($this->manifestCacheFile, $stash);
            return $this->stash = $stash;
        } else {
            return $this->stash = require $this->manifestCacheFile;
        }
    }


    public function getDepends()
    {
        if (isset($this->stash['depends'])) {
            return $this->stash['depends'];
        }
        return array();
    }

    /**
     * @return array the loaded manifest config array
     */
    public function getManifestConfig()
    {
        return $this->stash;
    }

    public function loadFromArray(array $stash)
    {
        $this->stash = $stash;
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
     */
    public function loadCollections(array $collectionStash)
    {
        $sourceDir = $this->sourceDir;
        $collections = array();

        // if the asset name contains the file type mark #js or #css
        //    jquery-ui:javascript
        //    jquery-ui:stylesheet
        //    jquery-ui#basic-theme
        $targetId = NULL;
        $targetType = NULL;
        if ($p = strpos($this->name, '#')) {
            $targetId = substr($this->name, $p + 1);
        }
        if ($p = strpos($this->name, ':')) {
            $targetType = substr($this->name, $p + 1);
        }

        foreach($collectionStash as $stash) {
            $collection = new Collection($stash);
            if (isset($stash['id']) ) {
                $collection->id = $stash['id'];
            }
            if (isset($stash['attrs']) ) {
                $collection->attributes = $stash['attrs'];
            }
            if( isset($stash['filters']) ) {
                $collection->filters = $stash['filters'];
            }
            if( isset($stash['compressors']) ) {
                $collection->compressors = $stash['compressors'];
            }

            $fileKey = $collection->initContentType($stash);
            // $collection->files = $this->expandFileList($sourceDir, $stash[$fileKey]);
            $collection->files = $stash[$fileKey];
            $collection->sourceDir = $this->getSourceDir();

            if ($targetId && $collection->id === $targetId) {
                return array($collection);
            }
            if ($targetType && $fileKey !== $targetType) {
                continue;
            }
            $collections[] = $collection;
        }
        return $collections;
    }

    public function expandFileList($sourceDir, $files) {
        $expandedFiles = array();
        foreach( $files as $p ) {
            // if we found a glob pattern
            if (strpos($p,'*') !== false) {

                $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);
                $expandedFiles = array_unique( array_merge( $expandedFiles , $expanded ) );

            } elseif(is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p )) {

                // We remove the base dir becase we need to build the 
                // asset urls
                $expanded = FileUtil::expand_dir_recursively( $sourceDir . DIRECTORY_SEPARATOR . $p );
                $expanded = FileUtil::remove_basedir_from_paths($expanded , $sourceDir);
                $expandedFiles = array_unique(array_merge( $expandedFiles , $expanded ));

            } else {
                $expandedFiles[] = $p;
            }
        }
        return $expandedFiles;
    }


    /**
     * @param integer Collection::FileType*
     */
    public function findCollectionsByFileType($filetype) {
        $collections = $this->getCollections();
        foreach($collections as $collection) {
            if ($filetype === $collection->filetype) {
                return $collection;
            }
        }
        return NULL;
    }

    /**
     * Find collection by specific ID
     */
    public function findCollectionById($id)
    {
        $collections = $this->getCollections();
        foreach($collections as $collection) {
            if ($id === $collection->id) {
                return $collection;
            }
        }
        return NULL;
    }


    public function getCollections()
    {
        if ($this->collections) {
            return $this->collections;
        }

        // load assets
        if (isset($this->stash['collections']) ) {
            return $this->collections = $this->loadCollections($this->stash['collections']);
        }
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


    private function _getFileListKey(array $stash) {
        foreach(array('file','js','css','javascript', 'coffeescript', 'coffee', 'sass', 'scss', 'stylesheet') as $key) {
            if (isset($stash[$key])) {
                return $key;
            }
        }
    }


    public function offsetSet($name,$value)
    {
        $this->stash[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->stash[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->stash[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->stash[$name]);
    }
    
}



