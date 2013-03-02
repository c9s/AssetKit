<?php
use AssetToolkit\Data;

class DataTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        is( Data::FORMAT_PHP, Data::detect_format_from_extension("path/to/file.php") );
        is( Data::FORMAT_JSON, Data::detect_format_from_extension("path/to/file.json") );
        is( Data::FORMAT_YAML, Data::detect_format_from_extension("path/to/file.yml") );
    }

    public function testPhpCompile()
    {
        ok(Data::compile_manifest_to_php("tests/assets/jquery/manifest.yml"));
    }

    public function testPhpEncode()
    {
        $a = array(
            'foo' => 123,
            'dirs' => array( 'a', 'b' ,'c' )
        );
        ok( Data::encode_file("tests/data",$a) );

        $b = Data::decode_file("tests/data");
        $this->assertEquals($a, $b);

        unlink("tests/data");
    }


    public function testYamlEncode()
    {
        $a = array(
            'foo' => 123,
            'dirs' => array( 'a', 'b' ,'c' )
        );
        ok( Data::encode_file("tests/data",$a, Data::FORMAT_YAML) );
        $b = Data::decode_file("tests/data", Data::FORMAT_YAML);
        $this->assertEquals($a, $b);
        unlink("tests/data");
    }

    public function testJsonEncode()
    {
        $a = array(
            'foo' => 123,
            'dirs' => array( 'a', 'b' ,'c' )
        );
        ok( Data::encode_file("tests/data",$a, Data::FORMAT_JSON) );
        $b = Data::decode_file("tests/data", Data::FORMAT_JSON);
        $this->assertEquals($a, $b);
        unlink("tests/data");
    }

}

