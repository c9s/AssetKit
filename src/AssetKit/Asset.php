<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;
use AssetKit\FileUtils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


/**
 * Asset class
 *
 * Asset object can be created from a manifest file.
 * Or can just be created with no arguments.
 */
class Asset
{
    public $stash;

    /* asset dir (related path, relate to config file) */
    public $sourceDir;


    /**
     * @var AssetKit\Config
     */
    public $config;

    public $collections = array();


    /**
     * @param array|string|null $arg manifest array, manifest file path, or asset name
     */
    public function __construct($arg = null)
    {
        // load from file.
        if( is_array($arg) ) {

        } elseif( is_string($arg) && file_exists($arg) ) {

        }

        // load from array
        if( $arg && is_array($arg) ) {
            $this->stash     = @$arg['stash'];
            $this->sourceDir       = @$arg['source_dir'] ?: @$arg['dir'];  // "dir" is for backward-compatible
            $this->name      = isset($arg['name']) ? $arg['name'] : null;
        }
        elseif( $arg && file_exists($arg) ) 
        {
            // load from file
            $file = $arg;
            $this->sourceDir = dirname($file);
            $this->name = basename(dirname($file));
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if( 'yml' === $ext ) {
                $serializer = new SerializerKit\Serializer('yaml');
                $this->stash = $serializer->decode(file_get_contents($file));
            } else {
                $this->stash = require $file;
            }

            // expand manifest glob pattern
            if( ! isset($this->stash['assets']) ) {
                throw new Exception('assets tag is not defined.');
            }
            else {
                $this->expandManifest();
            }

        }
        elseif( $arg && is_string($arg) ) {
            $this->name = $arg;
        }

        if( isset($this->stash['assets']) ) {
            $this->collections = FileCollection::create_from_manfiest($this);
        }
    }

    public function expandManifest()
    {
            foreach( $this->stash['assets'] as & $a ) {
                $dir = $this->sourceDir;
                $files = array();
                foreach( $a['files'] as $p ) {
                    if( strpos($p,'*') !== false ) {
                        $expanded = array_map(function($item) use ($dir) { 
                            return substr($item,strlen($dir) + 1);
                                 }, glob($this->sourceDir . DIRECTORY_SEPARATOR . $p));
                        $files = array_unique( array_merge( $files , $expanded ) );
                    }
                    elseif( is_dir( $dir . DIRECTORY_SEPARATOR . $p ) ) {
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



