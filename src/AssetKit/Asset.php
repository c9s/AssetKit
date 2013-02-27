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
            foreach( $stash['files'] as $p ) {


                // found glob pattern
                if( strpos($p,'*') !== false ) 
                {
                    $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);

                    // should be unique
                    $files = array_unique( array_merge( $files , $expanded ) );

                } elseif( is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p ) ) {

                    $expanded = FileUtil::expand_dir_recursively( $sourceDir . DIRECTORY_SEPARATOR . $p );
                    $expanded = FileUtil::remove_basedir_from_paths($expanded , $sourceDir);

                    $files = array_unique(array_merge( $files , $expanded ));

                } else {
                    $files[] = $p;
                }
            }
            // update filelist.
            $stash['files'] = $files;


            if( isset($stash['filters']) )
                $collection->filters = $stash['filters'];

            if( isset($stash['compressors']) ) {
                $collection->compressors = $stash['compressors'];
            }

            if( isset($stash['files']) ) {
                $collection->files = $stash['files'];
            }

            if( isset($stash['javascript']) || isset($stash['js']) ) {
                $collection->isJavascript = true;
            } elseif( isset($stash['stylesheet']) || isset($stash['css']) ) {
                $collection->isStylesheet = true;
            } elseif( isset($stash['coffeescript']) ) {
                $collection->isCoffeescript = true;
            }
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
        return $this->config->getBaseDir($absolute) . DIRECTORY_SEPARATOR . $this->name;
    }

    /**
     * Get the asset source directory
     *
     * @param bool $absolute
     */
    public function getSourceDir($absolute = false)
    {
        return $absolute
            ? $this->config->getRoot(true) . DIRECTORY_SEPARATOR . $this->sourceDir
            : $this->sourceDir
            ;
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



