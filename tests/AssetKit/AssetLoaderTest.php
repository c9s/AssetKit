<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$config = new AssetKit\Config('tests/config');
		$loader = new AssetKit\AssetLoader($config, array( 'assets') );
		$asset = $loader->load( 'jquery-ui' );
		ok( $asset );

		$collections = $asset->getFileCollections();
		foreach( $collections as $collection ) {
			$files = $collection->getFiles();
			ok( $files );
			foreach( $files as $file ) {
				file_exists($file);
			}
		}
		foreach( $collections as $collection ) {
			$content = $collection->getContent();
			ok( $content );
			ok( strlen( $content ) > 0 );
		}

		$loader->addCompressor('jsmin', function() {
			return new AssetKit\Compressor\JsMinCompressor;
		});
		$loader->addCompressor('cssmin', function() {
			return new AssetKit\Compressor\CssMinCompressor;
		});

		$writer = new AssetKit\AssetWriter( $loader );
		ok( $writer );

		$writer->from( array($asset) )
			->name( 'jquery-ui' )
			->in('tests/assets')
			->write();
	}
}

