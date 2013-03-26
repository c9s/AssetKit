<?php
namespace AssetToolkit;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\UniversalCache;

class Cache
{

    /**
     * Create default universal cache from config object.
     */
    static public function create($config)
    {
        $cache = new UniversalCache(array());

        // since APC is faster.
        if ( extension_loaded('apc') ) {
            $cache->addBackend( new ApcCache(array( 
                'namespace' => $config->getNamespace(),
            )));
        }

        $cache->addBackend( new FileSystemCache(array(  
            'cache_dir' => $config->getCacheDir(),
        )));
        return $cache;
    }
}

