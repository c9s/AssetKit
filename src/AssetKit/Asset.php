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

    /* manifest file (related path, relate to config file) */
    public $manifest;

    /* asset dir (related path, relate to config file) */
    public $dir;


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
        // load from array
        if( $arg && is_array($arg) ) {
            $this->stash     = @$arg['stash'];
            $this->manifest  = @$arg['manifest'];
            $this->dir       = @$arg['dir'];
            $this->name      = isset($arg['name']) ? $arg['name'] : null;
        }
        elseif( $arg && file_exists($arg) ) 
        {
            // load from file
            $file = $arg;

            $this->dir = dirname($file);
            $this->name = basename(dirname($file));
            $this->manifest = $file;

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
            foreach( $this->stash['assets'] as & $a ) {
                $dir = $this->dir;
                $files = array();
                foreach( $a['files'] as $p ) {
                    if( strpos($p,'*') !== false ) {
                        $expanded = array_map(function($item) use ($dir) { 
                            return substr($item,strlen($dir) + 1);
                                 }, glob($this->dir . DIRECTORY_SEPARATOR . $p));
                        $files = array_unique( array_merge( $files , $expanded ) );
                    }
                    elseif( is_dir( $dir . DIRECTORY_SEPARATOR . $p ) ) {
                        // expand files from dir
                        $ite = new RecursiveDirectoryIterator( $dir . DIRECTORY_SEPARATOR . $p );
                        $expanded = array();
                        foreach (new RecursiveIteratorIterator($ite) as $path => $info) {
                            if( $path === '.' || $path === '..' )
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
        elseif( $arg && is_string($arg) ) {
            $this->name = $arg;
        }

        if( isset($this->stash['assets']) ) {
            $this->collections = FileCollection::create_from_manfiest($this);
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
        return array(
            'stash' => $this->stash,
            'manifest' => $this->manifest,
            'dir'  => $this->dir,
            'name' => $this->name,
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

    public function getPathName()
    {
        return $this->dir;
    }

    public function getSourceDir($absolute = false)
    {
        if( $absolute ) {
            return $this->config->getRoot() . DIRECTORY_SEPARATOR . $this->dir;
        }
        return $this->dir;
    }

    /**
     * Return the public dir of this asset
     */
    public function getPublicDir($absolute = false)
    {
        $public = $this->config->getPublicRoot($absolute);
        return $public . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->name;
    }


    /**
     * Return asset base url
     */
    public function getBaseUrl()
    {
        return $this->config->getPublicAssetBaseUrl() . '/' . $this->name;
    }


    public function hasSourceFiles()
    {
        $this->dir;
        foreach( $this->collections as $collection ) {
            $paths = $collection->getSourcePaths(true);
            foreach( $paths as $path ) {
                if( ! file_exists($path) )
                    return false;
            }
        }
        return true;
    }




    public function initResource($update = false)
    {
        if( ! isset($this->stash['resource']) ) {
            return false;
        }

        // if we have the source files , we 
        // should skip initializing resource from remotes.
        if( ! $update && $this->hasSourceFiles() ) {
            return;
        }

        $resDir = null;
        $r = $this->stash['resource'];
        if( isset($r['url']) ) {
            $url = $r['url'];

            $info = parse_url($url);
            $path = $info['path'];
            $filename = basename($info['path']);
            $targetFile = $this->dir . DIRECTORY_SEPARATOR . $filename;

            echo "Downloading file...\n";
            $cmd = "curl -# --location " . escapeshellarg($url) . " > " . escapeshellarg($targetFile);
            system($cmd);

            echo "Stored at $targetFile\n";

            if( isset($r['zip']) ) {
                $zip = new ZipArchive;
                if( $zip->open( $targetFile ) === TRUE ) {
                    echo "Extracting to {$this->dir}\n";
                    $zip->extractTo( $this->dir );
                    $zip->close();
                    $resDir = $this->dir;
                    unlink( $targetFile );
                }
                else {
                    throw new Exception('Zip fail');
                }
            }
        }
        elseif( isset($r['github']) ) 
        {

            // read-only
            $url = 'git://github.com/' . $r['github'] . '.git';
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url,'.git');
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("git remote update --prune");
                system("git pull origin HEAD");
                chdir($dir);
            } else {
                system("git clone $url $resDir");
            }

        }
        elseif( isset($r['git']) ) 
        {
            $url = $r['git'];
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url,'.git');
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("git remote update --prune");
                system("git pull origin HEAD");
                chdir($dir);
            } else {
                system("git clone -q $url $resDir");
            }
        }
        elseif( isset($r['svn']) ) 
        {
            $url = $r['svn'];
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url);
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("svn update");
                chdir($dir);
            } else {
                system("svn checkout $url $resDir");
            }
        }
        elseif( isset($r['hg']) ) {
            $url = $r['hg'];
            $resDir = $this->dir . DIRECTORY_SEPARATOR . basename($url);
            if( file_exists($resDir) && $update ) {
                $dir = getcwd();
                chdir($resDir);
                system("hg pull -u");
                chdir($dir);
            } else {
                system("hg clone $url $resDir");
            }
        }

        // run commands for resources to initialize
        if( isset($r['commands']) ) {
            $cwd = getcwd();
            chdir( $resDir );
            foreach( $r['commands'] as $command ) {
                system($command);
            }
            chdir($cwd);
        }
    }
}



