AssetKit
============



    -AssetKit (master) % assetkit add assets/jquery/manifest.yml 
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


To use YUI Compressor:

    YUI_COMPRESSOR_BIN=utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar \
        assetkit add assets/test/manifest.yml

## Asset structure

	assets/jquery
	assets/jquery/manifest.php
	assets/jquery/manifest.yml
	assets/jquery/css/...
	assets/jquery/images/...
	assets/jquery/js/...
	assets/jqunit


## Requirement

* when using development mode, do not apply compressors.

* when using production mode, apply compressors.

* must-apply filters are: *.sass => CompassFilter, *.coffeescript => CoffeeScript filter

* when using compressors
  we can store the compressed content in a cached content.

* an asset may contains many file collection

* a file collection may contains many filters, compressors

* when not using compressors
  we simply rewrite css and image paths

  CSS path rewrite:
    * parse for image paths
    * copy these image path to webroot/assets/{asset name}/{image path}
    * replace image paths with /assets/{asset name}/{image path}

## Asset manifest

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

### Export static files to webroot

    $ assetkit export 

### Precompile assets

### Load asset manifest object

	$config = new AssetKit\Config('.assetkit');
    $loader = new AssetLoader( array( 'assets','other_assets')  );

	$assets = array();
    $assets[] = $loader->load('jquery');
    $assets[] = $loader->loadFile('assets/jquery/manifest.yml');

	$writer = new AssetKit\Writer;

    if( in production ) {
        $loader->addCompressorPattern('*.js', 'jsmin' );
        $loader->addCompressorPattern('*.css', 'cssmin' );
    }

    $writer->addFilterPattern('*.coffeescript', 'coffeescript' );
    $writer->addFilterPattern('*.sass', 'compass');

    $writer->addCompressor( 'jsmin' , function() {
        return new JsminCompressor( '/path/to/jsmin' );
    });

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
			->cache( 'apc' )
			->as( 'application' )
			->in( 'public/assets' );
			->write();

	// public/assets/images
	// public/assets/application-{md5}.css
	// public/assets/application-{md5}.js
	$manifest['stylesheet'];
	$manifest['stylesheet_file']; // local filepath
	$manifest['javascript'];
	$manifest['javascript_file']; // local filepath


    $asset = $loader->getAsset( 'jquery' );
    $fileCollections = $asset->getFileCollections();
    $filters = $asset->getFilters();

	foreach( $fileCollections as $collection ) {
		$content = $collection->output();
	}

### Asset Includer

    $include  = new AssetKit\AssetIncluder( $writer );
    $include->add( 'jquery-ui' );
    $include->add( 'jquery' );
    $include->render();

which renders:

    <link rel="stylesheet" href="...." type="text/css" media="screen"/>
    <script type="text/javascript" src="...."></script>

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

## Asset API

    $asset = Asset::fromYaml('path/to/yaml');

    $asset = new Asset;
    $asset->glob('public/js/*.js');
    $asset->dir('public/js/*.js');
    $asset->filter(function($content) { 
            return $content;
        });
    $asset->filter(new JsMinFilter);
    echo $asset->output(); // get contents and filter content.


    $loader = new AssetLoader;
    $loader['jquery'] = $asset;
    $loader['blueprint'] = $blueprint;

    echo $loader->output( 'jquery' );

    $loader = new AssetLoader;
    $loader->load( 'path/to/manifest.yml' );
    $loader->load( '.....' );


## Todo

* file mtime check
* fix baseDir for config



