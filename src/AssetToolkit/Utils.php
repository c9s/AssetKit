<?php
namespace AssetToolkit;
use Exception;

class Utils
{

    static function write_file($path,$content, $message = 'Write failed' ) {
        if( false === file_put_contents( $path , $content) ) {
            throw new Exception( $message . ' Path: ' . $path );
        }
    }

}



