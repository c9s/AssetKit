AssetKit
============

AssetKit is designed for PHP's performance, all configuration files are compiled into
PHP source code, this makes AssetKit loads these asset configuration files very quickly.

AssetKit is a powerful asset manager, provides a simple command-line
interface and a simple PHP library with simple API, there are many built-in
filters and compressors in it.

[![Build Status](https://travis-ci.org/c9s/php-AssetKit.png?branch=master)](https://travis-ci.org/c9s/php-AssetKit)



Concepts of AssetKit
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

- A "target" is consist of many assets, which is something like "page", we usually includes
  many assets in one single page, so called "target".

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
archtecture to organize these asset files. that is, AssetKit.

Features
---------------------------

- Centralized asset configuration.
- Automatically fetch & update your asset files.
- AssetCompiler: Compile multiple assets into one squashed file.
- AssetRender: Render compiled assets to HTML fragments, stylesheet tag or script tag.
- Command-line tool for installing, register, precompile assets.
- CSSMin compressor, YUI compressor, JSMin compressor, CoffeeScript, SASS, SCSS filters.
- APC cache support, which caches the compiled manifest, so you don't need to recompile them everytime.
- Twig Extension support. (see below)



Requirement
---------------------------

- yaml extension.

Installation
---------------------------

Install the requirements:

    $ gem install compass sass   # for sass/compass filter
    $ npm install coffee-script  # for coffee-script filter

Install library from composer:

```json
{
    "require": {
        "corneltek/assetkit": "~3"
    }
}
```

Get the command-line assetkit:

    $ curl -O https://raw.github.com/c9s/php-AssetKit/master/assetkit
    $ chmod +x assetkit
    $ sudo mv assetkit /usr/bin


The Asset Manifest File
-----------------------

To define file collections, you need to create a manifest.yml file in your asset directory,
for example, the backbonejs manifest.yml file:

```yaml
---
resource:
  url: http://backbonejs.org/backbone.js
collections:
  - js:
    - backbone.js
  - css:
    - app.css
  - sass:
    - home.sass
```

You can also define the resource, assetkit would fetch it for you. currently assetkit supports 
svn, git, hg resource types.



Basic Usage
-----------

Once you got `assetkit`, you can initialize it with your public path (web root):

```sh
$ assetkit init --baseDir "public/assets" --baseUrl "/assets" assetkit.yml
```

The config is stored at `assetkit.yml` file.

Then fetch anything you want:

```sh
$ assetkit add assets/jquery
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
```

And your `assetkit.yml` file will be updated, these asset files will be installed into `public/assets`.

>   NOTE:
>   To install asset files with symbol link, use --link option,
>   Which is convenient for asset development.

If someone wants to clone your project, you can add `assetkit.yml` file to the repository, then B can 
do `update` command to update assets:

```sh
$ assetkit update
```

To use assetkit in your application, just few lines to write:

```php
// load the autoload.php from composer
require 'vendor/autoload.php';

// load your asset config file, this contains asset manifest and types
$config = new AssetKit\AssetConfig( '../assetkit.yml', array( 
    'root' => APP_ROOT // the absolute path where you run "assetkit" command.
));

// initialize an asset loader
$loader = new AssetKit\AssetLoader( $config );

// load the required assets (of your page, application or controller)
$assets = $loader->loadAssets(array( 'jquery', 'jquery-ui' ));

// Use AssetRender to compile and render the HTML tag
$render = new AssetKit\AssetRender($config, $loader);

$targetName = 'demo-page';
$render->renderAssets($assets, $targetName); // pipe html tags to output buffer, the targetName is optional.
```

Now just load the script from your browser, it should work.

You may simply check example script in the `example` folder.



Advanced Usage
---------------------------

This creates and initializes the `assetkit.yml` file:

```sh
$ assetkit init --baseUrl=/assets --baseDir=public/assets --dir=private/assets assetkit.yml
```

Where the `--baseDir` option is the assets will be installed to.

Where the `--baseUrl` option is the assets can be loaded from front-end browser.

Where the `--dir` option is the location that you store your private asset files.

`assetkit.yml` is your config file, it's in YAML format, you can also modify it directly.


Register the assets you need:

```sh
$ assetkit add app/assets/jquery
$ assetkit add plugins/foo/assets/jquery-ui
$ assetkit add plugins/bar/assets/bootstrap
```



Then install asset resources into the `--baseDir` that you've setup:

```sh
$ assetkit install
```

There are two modes for installation, link and copy, to simply copy assets files
into the `baseDir`, we use default asset installer.

To symbol link assets to the `baseDir`, you may pass the `--link` flag.



Then integrate the AssetKit API into your PHP web application,
there are just few lines to write (you may check the `public/index.php` sample):

```php
use AssetKit\AssetConfig;
// Please install php-fileutil extension for beter performance, 

// To use AssetCompiler, AssetLoader or AssetRender, we need to initialize AssetConfig object.
$config = new AssetKit\AssetConfig( 'config/assetkit.yml',array(
    // the application root, contains the assetkit.yml file.
    'root' => APPLICATION_ROOT,
    'cache' => new UniversalCache\ApcCache(array( 'namespace' => 'myapp_' ));
));

$loader = new AssetKit\AssetLoader( $config );

$compiler = new AssetKit\AssetCompiler($config, $loader);
$compiler->enableProductionFstatCheck();
$compiler->defaultJsCompressor = 'uglifyjs';
$compiler->defaultCssCompressor = 'cssmin';

$assets = $loader->loadAssets(array( 'jquery', 'jquery-ui') );
$render = new AssetKit\AssetRender($config,$loader, $compiler);
$render->renderAssets($assets,'page-id');
```

The rendered result:

```html
<script type="text/javascript"  src="assets/demo/d95da0fbdccc220ccb5e4949a41ec796.min.js" ></script>
<link rel="stylesheet" type="text/css"  href="assets/demo/3fffd7e7bf5d2a459cad396bd3c375b4.min.css"/>
```

To update asset resource from remote (eg: git, github, hg or svn) if needed.

```sh
$ assetkit update
```

Once you've done, you can precompile the asset files to a squashed javascript/stylesheet files:

```sh
$ assetkit compile --target demo-page jquery jquery-ui
Notice: You may enable apc.enable_cli option to precompile production files from command-line.
Compiling assets to target 'demo-page'...
Stylesheet:
  MD5:  9399a997d354919cba9f84517eb7604a
  URL:  assets/demo-page/9399a997d354919cba9f84517eb7604a.min.css
  File: /Users/c9s/git/Work/AssetKit/public/assets/demo-page/9399a997d354919cba9f84517eb7604a.min.css
Javascript:
  MD5:   4a09100517e2d98c3f462376fd69d887
  URL:   assets/demo-page/4a09100517e2d98c3f462376fd69d887.min.js
  File:  /Users/c9s/git/Work/AssetKit/public/assets/demo-page/4a09100517e2d98c3f462376fd69d887.min.js
Done
```

You can also do:

    $ assetkit compile --target main --html-output head.php jquery

So that in your application, you can simple drop a line:

```php
<?php require "head.php"; ?>
```

You can also use the Twig Extension in your template:

```html+jinja
<html>
    <head>
    {% assets "jquery", "jquery-ui" as "my-home-page" %}
    </head>
</html>
```

To check all compiled target, you may simply run:

    $ assetkit target

To add assets to a target, you can run:

    $ assetkit target add demo-page jquery jquery-ui bootstrap

To remove a target, you can run:

    $ assetkit target remove demo-page

Setting Up Your Preferred Default Compressor
--------------------------------------------

Note that we've built-in uglifyjs compressor.

```php
$compiler->defaultJsCompressor = 'uglifyjs';
$compiler->defaultCssCompressor = 'cssmin';
$compiler->registerCompressor('uglifyjs', function() {
    return YourCompressor;
});
```

To use YUI Compressor
---------------------

    YUI_COMPRESSOR_BIN=utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar \
        assetkit add assets/test/manifest.yml


Filters
-------------------

### CoffeeScriptFilter

```php
$filter = new AssetKit\Filter\CoffeeScriptFilter;
```

### SassFilter

### ScssFilter

### CssImportFilter

### CssRewriteFilter


API
-------------------


### AssetConfig API

```php
$config = new AssetKit\AssetConfig('assetkit.yml',array(  
    'cache' => true,
    'cache_id' => 'your_app_id',
    'cache_expiry' => 3600
));
$config->setBaseUrl('/assets');
$config->setBaseDir('tests/assets');
$config->setEnvironment('production');

$baseDir = $config->getBaseDir(true); // absolute path
$baseUrl = $config->getBaseUrl();
$root = $config->getRoot();
$compiledDir = $config->getCompiledDir();
$compiledUrl = $config->getCompiledUrl();

$config->addAssetDirectory('vendor/assets');

$assetStashes = $config->all();

$config->save();
```


### AssetLoader API

```php
$loader = new AssetKit\AssetLoader($config);

// load asset from a directory that might contains a manifest file,
// Note: Since you're going to put the assetkit.yml file 
//       In your VCS, you should use relative path instead of 
//       absolute path.
$asset = $loader->register("tests/assets/jquery");

// load asset from a manifest file directly, 
$asset = $loader->loadFromManifestFile("tests/assets/jquery/manifest.yml");

// load multiple asset at one time
$assets = $loader->loadAssets(array('jquery','jquery-ui'));

$jquery = $loader->load('jquery');
$jqueryui = $loader->load('jquery-ui');

// get all loaded asset objects
$assets = $loader->all();

// get all loaded asset objects by pairs.
//   array( 'name' => [asset object], ... )
$assetMap = $loader->pairs();

// check if we've loaded the asset by asset name
if( $loader->has('jquery') ) {
    // do something here.
}

$updater = new ResourceUpdater;
$updater->update($asset);
```

### AssetInstaller API

```php
$installer = new AssetKit\Installer;
$installer->install( $asset );
$installer->uninstall( $asset );
```

```php
$installer = new AssetKit\LinkInstaller;
$installer->install( $asset );
$installer->uninstall( $asset );
```

### AssetCompiler API

```php
$asset = $loader->registerAssetFromPath("tests/assets/jquery-ui");
$compiler = new AssetKit\AssetCompiler($config,$loader);
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

```php
$render = new AssetKit\AssetRender($config,$loader);
$compiler = $render->getCompiler();
$compiler->enableProductionFstatCheck();
```

To enable builtin filters, compressors:

```php
$compiler->registerDefaultCompressors();
$compiler->registerDefaultFilters();
```

To register filters, compressors:

```php
$compiler->registerCompressor('jsmin', '\AssetKit\Compressor\JsMinCompressor');
$compiler->registerCompressor('cssmin', '\AssetKit\Compressor\CssMinCompressor');
$compiler->registerFilter( 'coffeescript','\AssetKit\Filter\CoffeeScriptFilter');
$compiler->registerFilter( 'css_import', '\AssetKit\Filter\CssImportFilter');
$compiler->registerFilter( 'sass', '\AssetKit\Filter\SassFilter');
$compiler->registerFilter( 'scss', '\AssetKit\Filter\ScssFilter');
```

### AssetRender API

This is the top level API to compile/render asset HTML tags, which 
operates AssetCompiler to compile loaded assets.

```php
$render = new AssetKit\AssetRender($config,$loader);
$render->renderAssets($assets,'demo');
```

Asset Twig Extension
--------------------

### Include stylesheets and javascripts in front-end page

Include specified asset:

```html+jinja
{% assets 'jquery' %}
```

Include multiple assets:

```html+jinja
{% assets "jquery", "jquery-ui" %}
```

Include multiple assets to the target:

```html+jinja
{% assets "jquery", "jquery-ui" as "jquery-all" %}
```

Hacking
----------------

Install deps:

```sh
$ git clone git://github.com/c9s/AssetKit.git
$ cd AssetKit
$ composer install --dev
```
Make sure all staff work:

```sh
phpunit
```

... Hack Hack Hack ...

Run tests again:

```sh
$ phpunit
```

Make sure command runs fine:

```sh
$ php bin/assetkit init --base-dir .... --base-url ....
$ php bin/assetkit compile --target mainpage jquery jquery-ui 
```
Re-Compile the command-line binary:

```sh
$ scripts/compile
```

Test the compiled binary, simply type:

```sh
$ ./assetkit
```


### Todos

- watch command (fork filter process to watch asset files or directories)

### Setup XHProf

You should install the xhprof extension from http://github.com/facebook/xhprof

Then setup hostname xhprof.dev to your xhprof_html directory.

Then run:

    pear install -f phpunit/PHPUnit_TestListener_XHProf
    phpunit -c phpunit-xhprof.xml

## The asset port manifest

The manifest.yml file:

```yaml
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
```

