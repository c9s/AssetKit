<?php
namespace AssetKit;
use AssetKit\AssetConfig;
use AssetKit\Asset;
use AssetKit\Collection;

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

    /**
     * Build urls for asset collection
     *
     * @param Asset $asset
     * @param Collection $collection
     *
     * @return path[]
     */
    public function buildCollectionUrls(Asset $asset, Collection $collection) {
        $urls = array();
        $baseUrl = $this->buildBaseUrl($asset);
        $paths = $collection->getFilePaths();
        foreach( $paths as $path ) {
            $urls[] = $baseUrl . '/' . $path;
        }
        return $urls;
    }

}




