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

    /* asset dir (related path, relate to config file) */
    public $sourceDir;


    /**
     * @var strign manifest file path
     */
    public $manifestFile;


    /**
     * @var AssetKit\Config
     */
    public $config;


    /**
     * @var AssetKit\FileCollection[]
     */
    public $collections = array();


    /**
     * @param array|string|null $arg manifest array, manifest file path, or asset name
     */
    public function __construct()
    {
    }

    public function loadFromManifestFile($manifestFile, $format = 0)
    {
        $config = null;
        if( $format ) {
            $config = Data::decode_file($manifestFile, $format);
        } else {
            $config = Data::detect_format_and_decode($manifestFile);
        }
        $this->manifestFile = $manifestFile;
        $this->sourceDir = dirname($manifestFile);
        $this->loadFromArray($config);
    }


    public function loadFromArray($config)
    {
        $this->stash = $config;
        // load assets
        if( isset($this->stash['assets']) ) {
            $this->expandPaths();
            $this->collections = FileCollection::create_from_asset($this->stash['assets']);
        }
    }

    /**
     * This expand glob patterns
     *
     */
    public function expandPaths()
    {
        foreach( $this->stash['collections'] as & $a )
        {
            $dir = $this->sourceDir;
            $files = array();
            foreach( $a['files'] as $p ) {

                // found glob pattern
                if( strpos($p,'*') !== false ) {

                    $expanded = FileUtil::expand_glob_from_dir($dir, $p);

                    // should be unique
                    $files = array_unique( array_merge( $files , $expanded ) );

                } elseif( is_dir( $dir . DIRECTORY_SEPARATOR . $p ) ) {

                    // expand files from dir
                    $ite = new RecursiveDirectoryIterator( $dir . DIRECTORY_SEPARATOR . $p );

                    $expanded = array();
                    foreach (new RecursiveIteratorIterator($ite) as $path => $info) {
                        if( $info->getFilename() === '.' || $info->getFilename() === '..' )
                            continue;
                        $expanded[] = $path;
                    }

                    $expanded = array_map(function($path) use ($dir) { 
                        return substr($path,strlen($dir) + 1);
                    } , $expanded);

                    $files = array_unique(array_merge( $files , $expanded ));
                } else {
                    $files[] = $p;
                }
            }
            $a['files'] = $files;
        }
    }

    public function createFileCollection()
    {
        $collection = new FileCollection;
        $collection->asset = $this;
        $collections[] = $collection;
        return $collection;
    }

    public function getFileCollections()
    {
        return $this->collections;
    }

    public function export()
    {
        // we should also save installed_dir
        // installed_dir = public dir + source dir
        return array(
            'stash'      => $this->stash,
            'manifest'   => $this->manifest,
            'source_dir' => $this->sourceDir,
            'name'       => $this->name,
        );
    }

    public function compile()
    {
        // compile assets
    }

    public function getName()
    {
        return $this->name;
    }

    public function getInstalledDir($absolute = false)
    {
        return $this->config->getPublicAssetRoot($absolute) . DIRECTORY_SEPARATOR . $this->name;
    }

    public function getSourceDir($absolute = false)
    {
        return $absolute
            ? $this->config->getRoot() . DIRECTORY_SEPARATOR . $this->sourceDir
            : $this->sourceDir
            ;
    }

    /**
     * Return the public dir of this asset
     *
     *   Asset public dir = Public dir + Asset source path
     *
     * @param bool $absolute should return absolute path or relative path ?
     */
    public function getPublicDir($absolute = false)
    {
        $public = $this->config->getPublicRoot($absolute);
        return $public . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->name;
    }


    /**
     * Check source file existence.
     *
     * @return bool
     */
    public function hasSourceFiles()
    {
        $this->sourceDir;
        foreach( $this->collections as $collection ) {
            $paths = $collection->getSourcePaths(true);
            foreach( $paths as $path ) {
                if( ! file_exists($path) )
                    return false;
            }
        }
        return true;
    }

    /**
     * Init Resource file and update to public asset dir ?
     */
    public function initResource($update = false)
    {
        $updater = new \AssetKit\ResourceUpdater($this);
        return $updater->update($update);
    }
}



