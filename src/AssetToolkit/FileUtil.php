<?php
namespace AssetToolkit;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use AssetToolkit\Data;

class FileUtil
{

    static function find_non_php_manifest_file_from_directory($dir) 
    {
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.json') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.yml') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
    }

    static function find_and_update_manifest_file_from_directory($dir)
    {
        // find cache
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.php') ) {
            $cache = $dir . DIRECTORY_SEPARATOR . 'manifest.php';
            $source = self::find_non_php_manifest_file_from_directory($dir);
            if( filemtime($cache) >= filemtime($source) ) {
                return $cache;
            }
        }

        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.json') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.yml') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.yml';

    }

    static function find_manifest_file_from_directory($dir) 
    {
        // find cache
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.php') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.php';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.json') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.yml') )
            return $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
    }


    static function compile_manifest_file_from_directory($dir)
    {
        if( file_exists($dir . DIRECTORY_SEPARATOR . 'manifest.php') )
            unlink( $dir . DIRECTORY_SEPARATOR . 'manifest.php' );
        $path = self::find_manifest_file_from_directory($dir);
        Data::compile_manifest_to_php($path);
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
            if ( $info->getFilename() === '.' || $info->getFilename() === '..' )
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


    static function rmtree( $paths )
    {
        $paths = (array) $paths;
        foreach( $paths as $path ) {
            if ( file_exists( $path ) ) {
                futil_rmtree($path);
            }
            if ( is_dir($path) ) {
                rmdir($path);
            } elseif ( is_file($path) ) {
                unlink($path);
            }
        }
        return true;
    }


}

