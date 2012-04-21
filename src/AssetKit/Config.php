<?php
namespace AssetKit;

class Config
{
    public $file;
    public $config = array();
    public $baseDir;

    public function __construct($file)
    {
        $this->file = $file;
        if( file_exists($file) ) {
            $this->config = json_decode(file_get_contents($file),true);
            $this->baseDir = dirname($file);
        }
    }

    public function addAsset($asset,$path)
    {
        if( ! $this->config['assets'] )
            $this->config['assets'] = array();
        $this->config['assets'][$asset] = $path;
    }

    public function removeAsset($asset)
    {
        unset($this->config['assets'][$asset]);
    }

    public function getAssetPath($name)
    {
        if( isset($this->config['assets'][$name] ) )
            return $this->config['assets'][$name];
    }

    public function save()
    {
        if( ! defined('JSON_PRETTY_PRINT') )
            define('JSON_PRETTY_PRINT',0);
        file_put_contents($this->file, json_encode($this->config, 
                JSON_PRETTY_PRINT));
    }

}

