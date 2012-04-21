<?php
namespace AssetKit;

class FileCollection
{

    public $filters = array();

    public $compressors = array();

    public $files = array();

    public $manifest;

    public function __construct()
    {

    }

    static function create_from_manfiest($manifest)
    {
        $assets = array();
        foreach( $manifest->stash['assets'] as $config ) {
            $asset = new self;
            if( isset($config['filters']) )
                $asset->filters = $config['filters'];

            if( isset($config['compressors']) )
                $asset->compressors = $config['compressors'];

            if( isset($config['files']) )
                $asset->files = $config['files'];

            $asset->manifest = $manifest;
            $assets[] = $asset;
        }
        return $assets;
    }
}



