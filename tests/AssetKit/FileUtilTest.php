<?php
use AssetKit\FileUtil;

class FileUtilTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $files = FileUtil::expand_glob_from_dir("tests/assets/jquery-ui/jquery-jquery-ui-27c4854/ui","*.js");
        ok($files);
        count_ok(31, $files);

        $files = FileUtil::expand_dir_recursively("tests/assets/jquery");
        foreach($files as $file) {
            path_ok( $file, $file );
        }
    }
}

