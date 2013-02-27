<?php
namespace AssetKit;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileUtil
{


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

}

