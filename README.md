AssetToolkit
============

AssetToolkit is different from Rails' asset pipeline, AssetToolkit is designed for PHP.

**What for!? Because we need a different strategy to compile/load assets for PHP web applications. 
you know, Rails is too slow, the same strategy might not be suitable in PHP applications.**

AssetToolkit is designed for PHP's performance, all configuration files are compiled into
PHP source code, this makes AssetToolkit loads these asset configuration files quickly.

AssetToolkit is a powerful asset manager, provides a simple command-line interface
and a simple PHP library, there are many built-in filters and compressors in it.

[![Build Status](https://travis-ci.org/c9s/php-AssetToolkit.png?branch=master)](https://travis-ci.org/c9s/php-AssetToolkit)

The Concept of AssetToolkit
---------------------------

- To improvement the asset loading performance, we register these wanted asset
  manifest files into an assetkit configuration file, which contains the asset
  source directory and other manifest file information. the config file is 
  converted into PHP source.

- When one asset is required from a web page, the asset can be quickly loaded
  through the AssetLoader, then the asset will be filtered through the filters
  and compiled/squashed to the front-end output. If the environment is
  production, the `AssetRenderer` will cache the url manifest for you, so you
  don't have to compile these assets everytime.

- In production mode, the asset compiler squashes the loaded asset files to the minified files.

- In development mode, the asset compiler simply render the include paths.

- You may define different required assets in each different page with a page id (target).

  The page id (target) is also used for caching results.

  So that in your product page, you may include `jquery`, `product` assets
  together with a page id "yourapp-products".  And in your main page, you may
  include `jquery`, `mainpage` assets with a page id "youapp-mainpage"

- One asset can have multiple file collections, the file collection can be css, scss, sass,
  coffee-script, live-script or javascript collection.

- Each file collection may have its own filters and compressors. so that a CSS file
  collection can use "cssmin" and "yuicss" compressor, and a SASS file collection 
  can use "sass" filter and "cssmin" compressor to generate the minified output.

Why do we separately loading the different assets and define the asset manifest ?
Because in the modern web application, most compononents are modularized, so 
in one application, there are many different plugins, modules, libraries, some
plugins might provide its own assets, but some don't. some assets need to be
compiled with some specific filters, but some don't. some assets need to be 
compressed through compressors like 'CSSMin' or 'JSMin', but some don't.

When developing front-end files, we usaually need to re-compile these asset
files again and again, and at the end, we still need to re-compile them into one
single squashed file to improve the front-end performance. And to re-compile these
files, some people use Makefile, some people use Grunt.js, but it's still hard
to configure, manage and distribute.

To give PHP applications a better flexibility, we designed a better
archtecture to organize these asset files. that is, AssetToolkit.

Features
---------------------------

- Centralized asset configuration.
- Automatically fetch & update your asset files.
- AssetCompiler: Compile multiple assets into one squashed file.
- AssetRender: Render compiled assets to HTML fragments, stylesheet tag or script tag.
- Command-line tool for installing, register, precompile assets.
- CSSMin compressor, YUI compressor, JSMin compressor, CoffeeScript, SASS, SCSS filters.
- APC cache support, which caches the compiled manifest, so you don't need to recompile them everytime.


Synopsis
---------------------------

This creates and initializes the `.assetkit.php` file:

    assetkit init --baseUrl=/assets --baseDir=public/assets

Register the assets you need:

    assetkit add app/assets/jquery
    assetkit add app/assets/jquery-ui
    assetkit add app/assets/bootstrap

Then install asset resources into the `--baseDir` you've setup:

    assetkit install

To update asset resource from remote (eg: git, github, hg or svn):

    assetkit update

Then integrate the AssetToolkit API into your PHP web application:

```php
$config = new AssetToolkit\AssetConfig( '../.assetkit.php',array( 
    // the application root, contains the .assetkit.php file.
    'root' => APPLICATION_ROOT,
    'environment' =>  AssetToolkit\AssetConfig::PRODUCTION,
));

$loader = new AssetToolkit\AssetLoader( $config );
$assets = array();
$assets[] = $loader->load( 'jquery' );
$assets[] = $loader->load( 'jquery-ui' );
$assets[] = $loader->load( 'test' );
$render = new AssetToolkit\AssetRender($config,$loader);
$render->renderAssets($assets,'page-id');
```

The rendered result:

```html
<script type="text/javascript"  src="assets/demo/d95da0fbdccc220ccb5e4949a41ec796.min.js" ></script>
<link rel="stylesheet" type="text/css"  href="assets/demo/3fffd7e7bf5d2a459cad396bd3c375b4.min.css"/>
```

Pre-compile targets:

    $ assetkit compile --target demo jquery jquery-ui
    Compiling assets to target 'demo'...
    Stylesheet:
      MD5:   9399a997d354919cba9f84517eb7604a
      URL:   assets/compiled/demo-9399a997d354919cba9f84517eb7604a.min.css
      File:  /Users/c9s/git/Work/AssetToolkit/public/assets/compiled/demo-9399a997d354919cba9f84517eb7604a.min.css
      Size:  59 KBytes
    Javascript:
      MD5:   4a09100517e2d98c3f462376fd69d887
      URL:   assets/compiled/demo-4a09100517e2d98c3f462376fd69d887.min.js
      File:  /Users/c9s/git/Work/AssetToolkit/public/assets/compiled/demo-4a09100517e2d98c3f462376fd69d887.min.js
      Size:  304 KBytes
    Done


Requirement
---------------------------

- APC extension.
- yaml extension.


Installation
---------------------------

```sh
git clone git@github.com:c9s/php-AssetToolkit.git
pear install -f package.xml
cp assetkit /usr/bin
```

The Asset Manifest File
---------------------------

To define file collections, you need to create a manifest.yml file in your asset directory,
for example, the backbonejs manifest.yml file:

```yaml
---
resource:
  url: http://backbonejs.org/backbone.js
assets:
  - js:
    - backbone.js
  - css:
    - app.css
  - sass:
    - home.sass
```

You can also define the resource, assetkit would fetch it for you. currently assetkit supports 
svn, git, hg resource types.


Usage
-----

Once you got `assetkit`, you can initialize it with your public path (web root):

    $ assetkit init --baseDir public/assets --baseUrl "/assets"

The config is stored at `.assetkit.php` file.

Then fetch anything you want:

    $ assetkit add assets/jquery/manifest.yml
    Submodule 'src/sizzle' () registered for path 'src/sizzle'
    Submodule 'test/qunit' () registered for path 'test/qunit'
    Submodule 'src/sizzle' () registered for path 'src/sizzle'
    Submodule 'test/qunit' () registered for path 'test/qunit'
    Checking jQuery against JSHint...
    JSHint check passed.
    jQuery Size - compared to last make
      252787      (-) jquery.js
       94771      (-) jquery.min.js
       33635      (-) jquery.min.js.gz
    jQuery build complete.
    Saving config...
    Done

And your `.assetkit` file will be updated, these asset files will be installed into `public/assets`.

>   NOTE:
>   To install asset files with symbol link, use --link option,
>   Which is convenient for asset development.

Once you've done, you can precompile the asset files to a squashed javascript/stylesheet files:

    $ assetkit compile --target demo-page jquery jquery-ui
    Notice: You may enable apc.enable_cli option to precompile production files from command-line.
    Compiling assets to target 'demo-page'...
    Stylesheet:
      MD5:  9399a997d354919cba9f84517eb7604a
      URL:  assets/demo-page/9399a997d354919cba9f84517eb7604a.min.css
      File: /Users/c9s/git/Work/AssetToolkit/public/assets/demo-page/9399a997d354919cba9f84517eb7604a.min.css
    Javascript:
      MD5:   4a09100517e2d98c3f462376fd69d887
      URL:   assets/demo-page/4a09100517e2d98c3f462376fd69d887.min.js
      File:  /Users/c9s/git/Work/AssetToolkit/public/assets/demo-page/4a09100517e2d98c3f462376fd69d887.min.js
    Done

If B wants to clone your project, please add `.assetkit` file to the repository, then B should 
do `update` command to update assets:

    $ assetkit update

You can simply include these files in your pages, or use the asset writer in your application.

To use assetkit in your application, just few lines to write:

```php
<?php
    // pick up a SPL classloader, we need this to load library files
    // you can check public/index.php for examples
    require 'bootstrap.php';
    define( ROOT , dirname(__DIR__) );

    // load your asset config file, this contains asset manifest and types
    $config = new AssetToolkit\AssetConfig( '../.assetkit', ROOT );

    // initialize an asset loader
    $loader = new AssetToolkit\AssetLoader( $config );

    // load the required assets (of your page, application or controller)
    $assets = array();
    $assets[]   = $loader->load( 'jquery' );
    $assets[]   = $loader->load( 'jquery-ui' );
    $assets[]   = $loader->load( 'extjs4-gpl' );
```


    To use YUI Compressor:

        YUI_COMPRESSOR_BIN=utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar \
            assetkit add assets/test/manifest.yml



The AssetConfig API
-------------------

```php
$config = new AssetToolkit\AssetConfig('.assetkit.php',array(  
    'cache' => true,
    'cache_id' => 'your_app_id',
    'cache_expiry' => 3600
));
$config->setBaseUrl('/assets');
$config->setBaseDir('tests/assets');

$baseDir = $config->getBaseDir(true); // absolute path
$baseUrl = $config->getBaseUrl();
$root = $config->getRoot();
$compiledDir = $config->getCompiledDir();
$compiledUrl = $config->getCompiledUrl();

$config->addAssetDirectory('vendor/assets');

$assetStashes = $config->getRegisteredAssets();

$config->save();
```


The AssetLoader API
-------------------

```php
$loader = new AssetToolkit\AssetLoader($config);
$asset = $loader->registerAssetFromPath("tests/assets/jquery");
$asset = $loader->registerFromManifestFile("tests/assets/jquery/manifest.yml");

$jquery = $loader->load('jquery');
$jqueryui = $loader->load('jquery-ui');

$updater = new ResourceUpdater;
$updater->update($asset);
```

The Asset Installer API
-----------------------

```php
$installer = new AssetToolkit\Installer;
$installer->install( $asset );
$installer->uninstall( $asset );
```

```php
$installer = new AssetToolkit\LinkInstaller;
$installer->install( $asset );
$installer->uninstall( $asset );
```

The AssetCompiler API
---------------------

```php
$asset = $loader->registerAssetFromPath("tests/assets/jquery-ui");
$compiler = new AssetToolkit\AssetCompiler($config,$loader);
$files = $compiler->compile($asset);

echo $files['js_url'];  //  outputs /assets/compiled/jquery-ui.min.js
echo $files['css_url']; //  outputs /assets/compiled/jquery-ui.min.css
```

When in production mode, the compiled manifest is cached in APC, to make 
AssetCompiler recompile your assets, you need to restart your HTTP server 
to clean up these cache.

We don't scan file modification time by default, because too many IO 
operations might slow down your application.

To auto-recompile these assets when you modified them, you can setup an
option to make your PHP application scan the modification time of asset files
to recompile assets:

```
$render = new AssetToolkit\AssetRender($config,$loader);
$render->setEnvironment( AssetToolkit\AssetConfig::PRODUCTION );
$compiler = $render->getCompiler();
$compiler->enableProductionFstatCheck();
```

The AssetRender API
--------------------

This is the top level API to compile/render asset HTML tags, which 
operates AssetCompiler to compile loaded assets.

```php
$render = new AssetToolkit\AssetRender($config,$loader);
$render->setEnvironment( AssetToolkit\AssetConfig::PRODUCTION );
$render->renderAssets($assets,'demo');
```


Hack
=======

Install deps:

    $ git clone git://github.com/c9s/AssetToolkit.git
    $ cd AssetToolkit
    $ onion install

... Hack Hack Hack ...

    $ php bin/assetkit

## The asset port manifest

The manifest.yml file:

    ---
    resource:
      git: git@github.com:blah/blah.git
    asset:
      - filters: [ "css_import" ]
        css:
        - css/*.sass
      - coffee:
        - js/*.coffee
      - js:
        - js/*
        - js/javascript.js


### Include assetkit in your application

Please check public/index.php file for example.


Asset Twig Extension
--------------------

### Include stylesheets and javascripts in front-end page

Include specified asset:

    {% assets 'jquery' %}

Include multiple assets:

    {% assets "jquery", "jquery-ui" %}

Include multiple assets to the target:

    {% assets "jquery", "jquery-ui" as "jquery-all" %}

