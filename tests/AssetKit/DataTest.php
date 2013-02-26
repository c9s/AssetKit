<?php
use AssetKit\Data;

class DataTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        is( Data::FORMAT_PHP, Data::detect_format("path/to/file.php") );
        is( Data::FORMAT_JSON, Data::detect_format("path/to/file.json") );
        is( Data::FORMAT_YAML, Data::detect_format("path/to/file.yml") );
        
    }
}

