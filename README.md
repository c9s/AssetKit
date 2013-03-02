AssetToolkit
============

AssetToolkit is different from Rails' asset pipeline, AssetToolkit is designed for PHP.

Because we need a different strategy to compile/load asset for PHP web applications.

AssetToolkit is designed for PHP's performance, all configuration files are compiled into
PHP source code, this makes AssetToolkit loads these asset configuration files very quick!

AssetToolkit is a powerful asset manager, provides a simple command-line interface
and a simple PHP library, there are many built-in filters and compressors in it.

The Concept of AssetToolkit
============================

- We register these wanted assets into the assetkit configuration file,
  which contains the asset source directory, manifest file information.

- When one asset is required from a web page, the asset can be quickly loaded through the AssetLoader, 
  then the asset will be filtered, compiled to the front-end output.

- In production mode, the asset compiler squash the loaded asset collection into minified files.

- In development mode, the asset compiler simply render the include paths for you.

- One asset can have multiple file collection, the file collection can be css,
  coffee-script, live-script, javascript collection.

- Each file collection has its own filter and compressor. so that CSS file
  collection can use "cssmin" and "yuicss" compressor, and SASS file collection 
  can use "sass" filter and "cssmin" compressor to generate the minified files.

Features
========

- Centralized asset configuration.
- Automatically fetch & update your asset files.
- AssetCompiler: Compile multiple assets into one squashed file.
- AssetRender: Render compiled assets to HTML fragments, stylesheet tag or script tag.
- Command-line tool for installing, register, precompile assets.
- CSSMin compressor, YUI compressor, JSMin compressor, CoffeeScript, SASS, SCSS filters.
- APC cache support


Synopsis
===========

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


Integaret the AssetToolkit API into your PHP web application:

```php
$config = new AssetToolkit\AssetConfig( '../.assetkit.php', ROOT);
$loader = new AssetToolkit\AssetLoader( $config );
$assets = array();
$assets[] = $loader->load( 'jquery' );
$assets[] = $loader->load( 'jquery-ui' );
$assets[] = $loader->load( 'test' );
$render = new AssetToolkit\AssetRender($config,$loader);
$render->setEnvironment( AssetToolkit\AssetRender::PRODUCTION );
$render->renderAssets('page-id',$assets);
```

Definitions
============

To define file collections, you need to create a manifest.yml file in your asset directory,
for example, the backbonejs manifest.yml file:

```yaml
---
resource:
  url: http://backbonejs.org/backbone.js
assets:
  - js: 1
    files:
    - backbone.js
```

You can also define the resource, assetkit would fetch it for you. currently assetkit supports 
svn, git, hg resource types.


Usage
=====

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

    $ assetkit compile --as your-app jquery jquery-ui blueprint
    Compiling...
    x /Users/c9s/git/Work/AssetToolkit/public/assets/your-app-f39c1144ad2911d574ec59d78329f2ba.js
    x /Users/c9s/git/Work/AssetToolkit/public/assets/your-app-c9f4db7954ea479dea822e0b665c1501.css
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

    // load your asset config file, this contains asset manifest and types
    $config = new AssetToolkit\AssetConfig( '../.assetkit');

    // initialize an asset loader
    $loader = new AssetToolkit\AssetLoader( $config );

    // load the required assets (of your page, application or controller)
    $assets = array();
    $assets[]   = $loader->load( 'jquery' );
    $assets[]   = $loader->load( 'jquery-ui' );
    $assets[]   = $loader->load( 'extjs4-gpl' );

    // initialize a cache (if you need one)
    $cache = new CacheKit\ApcCache( array('namespace' => 'demo') );

    $writer = new AssetToolkit\AssetWriter($config);
    $manifest = $writer
            ->cache($cache)
            ->production()          // generate for production code, (the alternative is `development`)
            ->name('app')
            ->write( $assets );

    // then use include renderer to render html for asset files
    $includer = new AssetToolkit\IncludeRender;
    $html = $includer->render( $manifest );

    // show html !
    echo $html;
```


    To use YUI Compressor:

        YUI_COMPRESSOR_BIN=utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar \
            assetkit add assets/test/manifest.yml

Hack
=======

Install deps:

    $ git clone git://github.com/c9s/AssetToolkit.git
    $ git submodule init
    $ git submodule update
    $ onion bundle

... Hack Hack Hack ...

    $ bash scripts/compile.sh
    $ ./assetkit


## The asset port manifest

The manifest.yml file:

    ---
    resource:
      git: git@github.com:blah/blah.git
    asset:
      - filters: [ "css_import" ]
        css:
        - css/*.sass
      - coffeescript:
        - js/*.coffee
      - js:
        - js/*
        - js/javascript.js


### Include assetkit in your application

Please check public/index.php file for example.


# Working in progress

### Can use create file collection directly

    $cln = new Collection;
    $cln->fromDir('path/to/dir');
    $cln->fromGlob('path/to/dir/*');
    $cln->addFile('path/to/file');

### Include stylesheets and javascripts in front-end page


Include specified asset:

    {% asset 'jquery' %}

Include all assets:

    {% asset '@all' %}

Include javascripts only:

    {% javascript 'jquery' %}
    {% javascript 'jquery-ui' %}

Include css only:

    {% stylesheet 'jquery-ui' %}
