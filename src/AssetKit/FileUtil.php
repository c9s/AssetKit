<?php
namespace AssetKit;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileUtil
{


    static function find_manifest_file_from_directory($dir) 
    {
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.php') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.php';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.json') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.yml') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
    }

    /**
     * Expand glob with the absolute path of asset source dir.
     * Returns relative path to the manifest.
     *
     * @param string $dir
     * @param string $glob
     *
     * @return array
     */
    static function expand_glob_from_dir($dir, $glob)
    {
        $files = glob($dir . DIRECTORY_SEPARATOR . $glob);
        return self::remove_basedir_from_paths($files,$dir);
    }



    /**
     * Remove base directory path from paths
     *
     * @param array $paths paths
     * @param string $basedir
     * @return array paths
     */
    static function remove_basedir_from_paths($paths,$basedir)
    {
        return array_map(function($item) use ($basedir) {
            return substr($item,strlen($basedir) + 1);
        }, $paths );
    }


    /**
     * Expand a directory by traverse it recursively.
     *
     * @param string $dir
     *
     * @return array
     */
    static function expand_dir_recursively($dir)
    {
        // expand files from dir
        $ite = new RecursiveDirectoryIterator($dir);
        $expanded = array();
        foreach (new RecursiveIteratorIterator($ite) as $path => $info) {
            if( $info->getFilename() === '.' || $info->getFilename() === '..' )
                continue;
            $expanded[] = $path;
        }
        return $expanded;
    }

    static function mkdir_for_file($file, $mask = 0755)
    {
        $dir = dirname($file);
        if( ! file_exists($dir) ) {
            return mkdir($dir, $mask , true);
        }
        return true;
    }

    static function get_extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    static function replace_extension($path,$replacement)
    {
        if( is_array($replacement) ) {
            $keys = array_keys($replacement);
            $key = end($keys);
            $val = $replacement[ $key ];
            return dirname($path) . DIRECTORY_SEPARATOR . 
                basename($path,$key) . $val;
        }
        elseif( is_string($replacement) ) {
            $parts = explode('.',$path);
            array_pop($parts);
            array_push($parts,$replacement);
            return join('.', $parts );
        }
        return $path;
    }

    static function rmtree( $paths )
    {
        $paths = (array) $paths;
        foreach( $paths as $path ) {
            if( ! file_exists( $path ) )
                throw new Exception( "$path does not exist." );

            if( is_dir( $path ) ) 
            {
                $iterator = new \DirectoryIterator($path);
                foreach ($iterator as $fileinfo) 
                {
                    if( $fileinfo->isDir() ) {
                        if(    $fileinfo->getFilename() === "." 
                            || $fileinfo->getFilename() === ".." )
                            continue;
                        self::rmtree( $fileinfo->getPathname() );
                    }
                    elseif ($fileinfo->isFile()) {
                        if( unlink( $fileinfo->getPathname() ) == false )
                            throw new Exception( "File delete error: {$fileinto->getPathname()}" );
                    }
                }
                rmdir( $path );
            } 
            elseif( is_file( $path ) ) {
                unlink( $path );
            }


        }
    }


}

