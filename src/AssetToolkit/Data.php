<?php
namespace AssetToolkit;
use AssetToolkit\FileUtil;

// should only run this when requiring.
// this is for 5.3 compatibility
if( ! defined('JSON_PRETTY_PRINT') )
    define('JSON_PRETTY_PRINT',0);

class Data
{
    const FORMAT_JSON = 1;
    const FORMAT_PHP  = 2;
    const FORMAT_YAML = 3;
    const FORMAT_UNKNOWN = 0;

    static function detect_format_from_extension($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch($ext) {
        case 'php':
            return self::FORMAT_PHP;
        case 'json':
            return self::FORMAT_JSON;
        case 'yml':
        case 'yaml':
            return self::FORMAT_YAML;
        }
        return self::FORMAT_UNKNOWN;
    }

    static function compile_manifest_to_php($path, $format = 0)
    {
        if( $format ) {
            $data = self::decode_file($path, $format);
        } else {
            $data = self::detect_format_and_decode($path);
        }

        // $newpath = FileUtil::replace_extension($path,"php");
        $newpath = \futil_replace_extension($path,"php");
        $ret = self::encode_file( $newpath , $data , self::FORMAT_PHP );
        if( $ret !== false )
            return $newpath;
    }

    static function detect_format_and_decode($path)
    {
        if($format = self::detect_format_from_extension($path)) {
            return self::decode_file($path,$format);
        }
    }

    static function decode_file($file, $format = self::FORMAT_PHP)
    {
        if($format === self::FORMAT_PHP ) {
            return require($file);
        } elseif ($format === self::FORMAT_JSON ) {
            return json_decode(file_get_contents($file),true);
        } elseif ($format === self::FORMAT_YAML ) {
            return yaml_parse_file($file);
        }
    }

    static function encode_file($path, $data, $format = self::FORMAT_PHP) 
    {
        if( $format === self::FORMAT_JSON ) {
            return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        } else if ($format === self::FORMAT_PHP ) {
            $php = '<?php return ' .  var_export($data,true) . ';';
            return file_put_contents($path, $php);
        } else if ($format === self::FORMAT_YAML ) {
            return file_put_contents($path, yaml_emit($data));
        }
    }
}

