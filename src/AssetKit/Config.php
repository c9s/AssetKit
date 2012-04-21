<?php
namespace AssetKit;

class Config
{
    public $config;

    public function __construct($file)
    {
        $this->config = json_decode($file);
    }

}

