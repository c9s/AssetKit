<?php
namespace AssetKit;

/**
 * run through all css files
 *
 * find images and put them in custom path public/asset
 */
class CssImagePreprocess
{
    public $assets;

    public function from($assets)
    {
        $this->assets = $assets;
        return $this;
    }

    public function to($dir)
    {

    }
}


