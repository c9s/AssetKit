AssetKit
============

AssetKit is powerful asset manager, provides a simple command-line interface
and a simple PHP library, AssetKit has many built-in filters and compressors for asset files.

AssetKit can fetch asset ports and initialize them from a simple manifest YAML file.

You can use AssetKit library to integrate assets for your web applications very easily.


Usage
=====

Once you got `assetkit`, you can initialize it with your public path (web root):

    $ assetkit init --public public

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

Once you've done, you can precompile the assets to a squashed javascript/stylesheet files:

    $ assetkit compile --as your-app jquery jquery-ui blueprint
    Compiling...
    x /Users/c9s/git/Work/AssetKit/public/assets/your-app-f39c1144ad2911d574ec59d78329f2ba.js
    x /Users/c9s/git/Work/AssetKit/public/assets/your-app-c9f4db7954ea479dea822e0b665c1501.css
    Done

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
    $ composer.phar install  # install Symfony Process

... Hack Hack Hack ...

    $ bash scripts/compile.sh
    $ ./assetkit


## The asset port manifest

The manifest.yml file:

    ---
    resource:
      git: git@github.com:blah/blah.git
    asset:
      - filters: [ "compass" ]
        files:
          - css/*.sass
      - filters: [  ]
        files:
          - js/*
          - js/javascript.js


## Use flow

### Fetch remote resource and include to asset config

    $ assetkit init 

    $ assetkit add assets/jquery/manifest.yml

fetch resource and extract it

### Pre-compile & export static files to webroot

    $ assetkit compile --as app jquery-ui jquery


### Include assetkit in your application

Please check public/index.php file for example.




### Asset Library API

    $config = new AssetKit\Config('.assetkit');
    $loader = new AssetLoader( $config , array( 'assets','other_assets')  );

    $assets = array();
    $jquery = $loader->load('jquery');
    $jqueryui = $loader->loadFile('assets/jquery-ui/manifest.yml');

    $writer = new AssetKit\Writer($config);

    if( in production ) {
        $loader->addCompressorPattern('*.js', 'jsmin' );
        $loader->addCompressorPattern('*.css', 'cssmin' );
    }

    $writer->addFilterPattern('*.coffeescript', 'coffeescript' );
    $writer->addFilterPattern('*.sass', 'compass');

    $writer->addFilter( 'compass', function() {
        return new Compass( '/path/to/compass' );
    });


    $writer->addFilter( 'css_rewrite' , function() {
        return new CssRewriteFilter(array( 
            'base' => '/assets',
            'dir' => 'public/assets',
        ));
    });

    // parse css image files and copy to public/assets
    $cssImagePreprocess = new CssImagePreprocess;
    $cssImagePreprocess->from( $assets )
            ->in( 'public/assets' )
            ->process();

    $writer = new AssetKit\AssetWriter( );
    $manifest = $writer->from( $assets )
            ->cache( $cache )
            ->as( 'application' )
            ->in( 'public/assets' );
            ->write();

    // public/assets/images
    // public/assets/application-{md5}.css
    // public/assets/application-{md5}.js


    $asset = $loader->getAsset( 'jquery' );
    $fileCollections = $asset->getFileCollections();
    $filters = $asset->getFilters();

    foreach( $fileCollections as $collection ) {
        $content = $collection->output();
    }

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
