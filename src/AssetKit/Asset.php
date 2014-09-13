<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AssetKit\FileUtil;
use AssetKit\FileUtils;
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
     * @var AssetKit\Collection[]
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
        $this->manifestFile = $manifestYamlFile;
        $this->sourceDir    = dirname($manifestYamlFile);

        $config = array();
        $compiledFile = ConfigCompiler::compiled_filename($manifestYamlFile);
        if (ConfigCompiler::test($manifestYamlFile, $compiledFile)) {
            // do config compile
            $config = ConfigCompiler::parse($manifestYamlFile);

            // expand file list
            foreach($config['collections'] as & $cStash) {
                $key = $this->_getFileListKey($cStash);
                $cStash[$key] = $this->expandFileList($this->sourceDir, $cStash[$key]);
            }

            // write config back
            ConfigCompiler::write($compiledFile,$config);
        } else {
            $config = require $compiledFile;
        }

        if (isset($config['name'])) {
            $this->name = $config['name'];
        } else {
            $this->name         = basename($this->sourceDir);
        }
        $this->stash = $config;
        // $this->loadFromArray($config);
    }

    public function loadFromArray($config)
    {
        $this->stash = $config;
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
            $collections[] = $collection;
        }
        return $collections;
    }

    public function expandFileList($sourceDir, $files) {
        $expandedFiles = array();
        foreach( $files as $p ) {
            // if we found a glob pattern
            if( strpos($p,'*') !== false )
            {
                $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);
                $expandedFiles = array_unique( array_merge( $expandedFiles , $expanded ) );

            } elseif( is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p ) ) {
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




    public function getCollections()
    {
        if ($this->collections) {
            return $this->collections;
        }

        // load assets
        if( ! isset($this->stash['collections']) ) {
            throw new Exception("the 'collections' is not defined in {$this->name}");
        }
        return $this->collections = $this->loadCollections($this->stash['collections']);
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


    private function _getFileListKey($stash) {
        foreach(array('files','js','css','javascript', 'coffeescript', 'coffee', 'sass', 'scss', 'stylesheet') as $key) {
            if (isset($stash[$key])) {
                return $key;
            }
        }
    }
}



