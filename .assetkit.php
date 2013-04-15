<?php return array (
  'baseDir' => 'public/assets',
  'baseUrl' => '/assets',
  'dirs' => 
  array (
    0 => 'assets',
  ),
  'assets' => 
  array (
    'jquery' => 
    array (
      'manifest' => 'tests/assets/jquery/manifest.php',
      'source_dir' => 'tests/assets/jquery',
      'name' => 'jquery',
    ),
    'jquery-ui' => 
    array (
      'manifest' => 'tests/assets/jquery-ui/manifest.php',
      'source_dir' => 'tests/assets/jquery-ui',
      'name' => 'jquery-ui',
    ),
    'test' => 
    array (
      'manifest' => 'tests/assets/test/manifest.php',
      'source_dir' => 'tests/assets/test',
      'name' => 'test',
    ),
    'underscore' => 
    array (
      'manifest' => 'tests/assets/underscore/manifest.php',
      'source_dir' => 'tests/assets/underscore',
      'name' => 'underscore',
    ),
  ),
  'target' => 
  array (
    'demo' => 
    array (
      0 => 'jquery',
      1 => 'jquery-ui',
      2 => 'underscore',
      3 => 'test',
    ),
    'demo-page' => 
    array (
      0 => 'jquery',
      1 => 'jquery-ui',
    ),
  ),
  'namespace' => 'assetkit',
);