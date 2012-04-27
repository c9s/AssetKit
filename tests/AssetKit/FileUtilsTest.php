<?php

class FileUtilsTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $new = AssetKit\FileUtils::replace_extension( 'path/to/file.min.coffee', array(
                'coffee' => 'js' ));

        is( 'path/to/file.min.js', $new );
        $new = AssetKit\FileUtils::replace_extension( 'path/to/file.min.coffee', 'js' );
        is( 'path/to/file.min.js', $new );
    }
}

