<?php
namespace AssetKit;

class FileUtils
{

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
}



