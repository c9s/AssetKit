<?php
require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
define( 'ROOT', dirname(dirname(__FILE__) ));
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', ROOT . '/vendor/pear',
));

$baseUrl = dirname($_SERVER['SCRIPT_NAME']);

$classLoader->useIncludePath(false);
$classLoader->register();

$config = new AssetToolkit\AssetConfig( '../.assetkit.php', ROOT);
$loader = new AssetToolkit\AssetLoader( $config );

$assets = array();
$assets[] = $loader->load( 'jquery' );
$assets[] = $loader->load( 'jquery-ui' );
$assets[] = $loader->load( 'test' );
$render = new AssetToolkit\AssetRender($config,$loader);
$render->setEnvironment( AssetToolkit\AssetRender::PRODUCTION );
?>
<html>
<head>
<?php
$render->renderAssets('demo',$assets);
?>
</head>
<body>
<?php
var_dump( $_SERVER['PATH'] ); 
?>
</body>
</html>
