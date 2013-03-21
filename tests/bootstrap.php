<?php
require 'vendor/autoload.php';
require 'PHPUnit/TestMore.php';

if( extension_loaded('apc') ) {
    apc_clear_cache();
}
if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}

// from c9s/php-fileutil
if ( ! extension_loaded('fileutil') ) {
    require "FileUtil.php";
}
