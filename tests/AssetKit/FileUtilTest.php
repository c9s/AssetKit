<?php
use AssetKit\FileUtil;

class FileUtilTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $files = FileUtil::expand_glob_from_dir("tests/assets/jquery-ui/jquery-ui-1.10.1.custom/development-bundle/ui","*.js");
        ok($files);
        count_ok(35, $files);

        $files = FileUtil::expand_dir_recursively("tests/assets/jquery");
        foreach($files as $file) {
            path_ok( $file, $file );
        }
    }
}

