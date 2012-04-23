<?php
namespace AssetKit;
use Exception;

class FileCollection
{

    public $filters = array();

    public $compressors = array();

    public $files = array();

    public $asset;

	public $isJavascript;

	public $isStylesheet;

	public $content;

    public function __construct()
    {

    }

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

			if( isset($config['javascript']) )
				$collection->isJavascript = true;

			if( isset($config['stylesheet']) )
				$collection->isStylesheet = true;

            $collection->asset = $asset;
            $collections[] = $collection;
        }
        return $collections;
    }

	public function getFiles()
	{
		$dir = $this->asset->dir;
		$baseDir = $this->asset->config->baseDir;
		return array_map( function($file) use($dir,$baseDir){ 
				return $baseDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
			}, $this->files );
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

    public function getFiles()
    {
        return $this->files;
    }

	public function getContent()
	{
		if( $this->content )
			return $this->content;

		$content = '';
		foreach( $this->getFiles() as $file ) {
            if( ! file_exists($file) )
                throw new Exception("$file does not exist.");
			$content .= file_get_contents( $file );
		}
		return $this->content = $content;
	}

}

