<?php
namespace AssetToolkit;
use AssetToolkit\AssetConfig;
use AssetToolkit\Asset;

class AssetUrlBuilder
{
    protected $config;

    public function __construct(AssetConfig $config) {
        $this->config = $config;
    }

    /**
     * Build Asset base url
     *
     * @param Asset $asset The asset object.
     */
    public function buildBaseUrl(Asset $asset) {
        return $this->config->getBaseUrl() . '/' . $asset->name;
    }
}




