AssetKit
============

AssetKit is powerful asset manager, provides a simple command-line interface
and a simple PHP library, AssetKit has many built-in filters and compressors for asset files.

AssetKit can fetch asset ports and initialize them from a simple manifest YAML file.

You can use AssetKit library to integrate assets for your web applications very easily.

Features
========

- CSSMin compressor
- YUI compressor
- JSMin compressor
- CoffeeScript filter
- APC cache support

Installation
============

    $ pear channel-discover pear.corneltek.com
    $ pear install corneltek/AssetKit


Definitions
============
To use AssetKit, you have to know some basic component concepts in AssetKit.

One asset can have multiple file collection, the file collection can be css,
coffee-script, live-script, javascript collection.

Each file collection has its own filter and compressor. so that CSS file
collection can use "cssmin" and "yuicss" compressor, and SASS file collection 
can use "sass" filter and "cssmin" compressor to generate the minified files.

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

    $ assetkit init --public public

The config is stored at `.assetkit` file.

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
    x /Users/c9s/git/Work/AssetKit/public/assets/your-app-f39c1144ad2911d574ec59d78329f2ba.js
    x /Users/c9s/git/Work/AssetKit/public/assets/your-app-c9f4db7954ea479dea822e0b665c1501.css
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
    $config = new AssetKit\Config( '../.assetkit');

    // initialize an asset loader
    $loader = new AssetKit\AssetLoader( $config );

    // load the required assets (of your page, application or controller)
    $assets = array();
    $assets[]   = $loader->load( 'jquery' );
    $assets[]   = $loader->load( 'jquery-ui' );
    $assets[]   = $loader->load( 'extjs4-gpl' );

    // initialize a cache (if you need one)
    $cache = new CacheKit\ApcCache( array('namespace' => 'demo') );

    $writer = new AssetKit\AssetWriter($config);
    $manifest = $writer
            ->cache($cache)
            ->production()          // generate for production code, (the alternative is `development`)
            ->name('app')
            ->write( $assets );

    // then use include renderer to render html for asset files
    $includer = new AssetKit\IncludeRender;
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

    $ git clone git://github.com/c9s/AssetKit.git
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
      - css: 1
        filters: [ "css_import" ]
        files:
          - css/*.sass
      - coffeescript: 1
        files:
          - js/*.coffee
      - js: 1
        files:
          - js/*
          - js/javascript.js


### Include assetkit in your application

Please check public/index.php file for example.


# Working in progress

### Can use create file collection directly

    $cln = new FileCollection;
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
