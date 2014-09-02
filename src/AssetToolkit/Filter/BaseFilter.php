<?php
namespace AssetToolkit\Filter;
use AssetToolkit\AssetUrlBuilder;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;

class BaseFilter
{
    protected $config;

    public function __construct(AssetConfig $config) { 
        $this->config = $config;
    }
}

