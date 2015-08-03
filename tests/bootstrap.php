<?php
$autoloader = require 'vendor/autoload.php';

if (extension_loaded('apc')) {
    apc_clear_cache();
}
if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}

if (file_exists('tests/public/compiled')) {
    futil_rmtree('tests/public/compiled');
    mkdir('tests/public/compiled', 0755, true);
}

return $autoloader;
