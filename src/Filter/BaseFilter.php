<?php
namespace AssetKit\Filter;
use AssetKit\AssetUrlBuilder;
use AssetKit\AssetConfig;
use AssetKit\Asset;
use AssetKit\Collection;

abstract class BaseFilter
{
    protected $config;

    public function __construct(AssetConfig $config) { 
        $this->config = $config;
    }

    abstract public function filter(Collection $collection);
}

