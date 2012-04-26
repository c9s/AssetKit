<?php
require '../vendor/autoload.php';
require '../vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
define( 'ROOT', dirname(dirname(__FILE__) ));
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array(
    ROOT . '/src', ROOT . '/vendor/pear',
));
$classLoader->useIncludePath(false);
$classLoader->register();

$config = new AssetKit\Config( ROOT . '/.assetkit');
$loader = new AssetKit\AssetLoader( $config , array( ROOT . '/assets' ) );
$jquery = $loader->load( 'jquery' );
$jqueryui = $loader->load( 'jquery-ui' );

$assets = array();
$assets[] = $jquery;
#  $assets[] = $jqueryui;


$writer = new AssetKit\AssetWriter($config);
$manifest = $writer->name('app')
        ->writeForProduction( $assets );

$includer = new AssetKit\IncludeRender;
$html = $includer->render( $manifest );
?>
<html>
<head>
    <?=$html?>
</head>
<body>
<?php
var_dump( $manifest );
var_dump( $html );
?>
</body>
</html>
