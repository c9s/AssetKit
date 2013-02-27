<?php
require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
define( 'ROOT', dirname(dirname(__FILE__) ));
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', ROOT . '/vendor/pear',
));

$baseUrl = dirname($_SERVER['SCRIPT_NAME']);

$classLoader->useIncludePath(false);
$classLoader->register();

$config = new AssetKit\AssetConfig( ROOT . '/.assetkit');
$loader = new AssetKit\AssetLoader( $config , array( ROOT . '/assets' ) );

$assets = array();
$assets[] = $loader->load( 'jquery' );
$assets[] = $loader->load( 'jquery-ui' );
$assets[] = $loader->load( 'test' );

$cache = new CacheKit\ApcCache( array('namespace' => 'demo') );
$writer = new AssetKit\AssetWriter($config);
$manifest = $writer->name('app')
        // ->cache($cache)
        ->production()
        ->write( $assets );

$includer = new AssetKit\IncludeRender;
$head = $includer->render( $manifest );
?>
<html>
<head>
    <?=$head?>
</head>
<body>
<?php
var_dump( $_SERVER['PATH'] ); 
var_dump( $manifest );
?>
</body>
</html>
