<?php
namespace AssetKit\Filter;

class CoffeeScriptFilter
{

    function __construct($coffeescript, $nodejs = 'node' )
    {
        // code...
    }

	public function filter($collection)
	{
		$files = $collection->getFiles();
	}

}


