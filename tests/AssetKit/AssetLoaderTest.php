<?php

class AssetLoaderTest extends PHPUnit_Framework_TestCase
{
	function test()
	{
		$config = new AssetKit\Config('tests/config');
		$loader = new AssetKit\AssetLoader( $config , array( 'assets' ));
		$manifest = $loader->load( 'jquery-min' );

		ok( $manifest );
	}
}

