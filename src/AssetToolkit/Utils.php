<?php
namespace AssetToolkit;
use Exception;

class Utils
{

    static function findbin($bin)
    {
        $path = getenv('PATH') . ':/usr/local/bin:/opt/local/bin';
        $paths = explode(':',$path);
        foreach( $paths as $path ) {
            if ( file_exists($path . DIRECTORY_SEPARATOR . $bin ) ) {
                return $path . DIRECTORY_SEPARATOR . $bin;
            }
        }
        return false;
    }

    static function write_file($path,$content, $message = 'Write failed' ) {
        if( false === file_put_contents( $path , $content) ) {
            throw new Exception( $message . ' Path: ' . $path );
        }
    }

}



