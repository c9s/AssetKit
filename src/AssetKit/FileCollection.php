<?php
namespace AssetKit;
use Exception;
use IteratorAggregate;

class FileCollection
    implements IteratorAggregate
{

    public $filters = array();

    public $compressors = array();

    public $files = array();

    public $asset;

    public $isJavascript;

    public $isStylesheet;

    public $isCoffeescript;

    public $content;

    static function create_from_manfiest($asset)
    {
        $collections = array();
        foreach( $asset->stash['assets'] as $config ) {
            $collection = new self;
            if( isset($config['filters']) )
                $collection->filters = $config['filters'];

            if( isset($config['compressors']) )
                $collection->compressors = $config['compressors'];

            if( isset($config['files']) ) {
                $collection->files = $config['files'];
            }

            if( isset($config['javascript']) || isset($config['js']) ) {
                $collection->isJavascript = true;
            } elseif( isset($config['stylesheet']) || isset($config['css']) ) {
                $collection->isStylesheet = true;
            } elseif( isset($config['coffeescript']) ) {
                $collection->isCoffeescript = true;
            }

            $collection->asset = $asset;
            $collections[] = $collection;
        }
        return $collections;
    }

    public function getSourcePaths($absolute = false)
    {
        if( ! $this->asset ) {
            throw new Exception("file collection requires asset object, but it's undefined.");
        }

        $dir = $this->asset->getSourceDir($absolute);
        return array_map(function($file) use ($dir) {
                return $dir . DIRECTORY_SEPARATOR . $file;
            }, $this->files);
    }


    public function getFilePaths()
    {
        return $this->files;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function addFile($path)
    {
        $this->files[] = $path;
        return $this;
    }


    public function hasCompressor($name)
    {
        return in_array( $name, $this->compressors );
    }

    public function hasFilter($name)
    {
        return in_array( $name, $this->filters );
    }

    public function getCompressors()
    {
        return $this->compressors;
    }

    public function getFilters()
    {
        return $this->filters;
    }


    public function addFilter($filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function getLastModifiedTime()
    {
        if( ! empty($this->files) ) {
            $mtimes = array_map( function($file) { 
                return filemtime($file); }, $this->getSourcePaths() );
            rsort($mtimes, SORT_NUMERIC);
            return $mtimes[0];
        }
    }

    public function getContent()
    {
        if( $this->content )
            return $this->content;

        $content = '';
        foreach( $this->getSourcePaths(true) as $file ) {
            if( ! file_exists($file) )
                throw new Exception("$file does not exist.");
            $content .= file_get_contents( $file );
        }
        return $this->content = $content;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getSourcePaths(true));
    }

}

