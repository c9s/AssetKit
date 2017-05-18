<?php
namespace AssetKit;
use UniversalCache\ApcuCache;
use UniversalCache\FileSystemCache;
use UniversalCache\UniversalCache;
use AssetKit\AssetConfig;

class CacheFactory
{

    /**
     * Create default universal cache from config object.
     */
    static public function create(AssetConfig $config)
    {
        $cache = new UniversalCache(array());
        if (extension_loaded('apcu')) {
            $cache->addBackend(new ApcuCache($config->getNamespace()));
        }
        $cache->addBackend(new FileSystemCache($config->getCacheDir()));
        return $cache;
    }
}

