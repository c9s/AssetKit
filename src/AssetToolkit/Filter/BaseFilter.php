<?php
namespace AssetToolkit\Filter;
use AssetToolkit\AssetUrlBuilder;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;
use AssetToolkit\Collection;

abstract class BaseFilter
{
    protected $config;

    public function __construct(AssetConfig $config) { 
        $this->config = $config;
    }

    abstract public function filter(Collection $collection);
}

