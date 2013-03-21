<?php
require 'vendor/autoload.php';

if( extension_loaded('apc') ) {
    apc_clear_cache();
}
if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}
