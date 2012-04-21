<?php
namespace AssetKit;

class Config
{
    public $file;
    public $config;

    public function __construct($file)
    {
        $this->file = $file;
        $this->config = json_decode($file,true);
    }

    public function addAsset($asset,$path)
    {
        if( ! $this->config['assets'] )
            $this->config['assets'] = array();
        $this->config['assets'][$asset] = $path;
    }

    public function save()
    {
        defined('JSON_PRETTY_PRINT') || define('JSON_PRETTY_PRINT',0);
        file_put_contents($this->file, json_encode($this->config, JSON_PRETTY_PRINT));
    }

}

