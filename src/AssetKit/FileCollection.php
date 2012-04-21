<?php
namespace AssetKit;

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

            if( isset($config['files']) )
                $collection->files = $config['files'];

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
		static $files;
		if( $files )
			return $files;
		return $files = array_map( function($file) use($dir,$baseDir){ 
				return $baseDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
			}, $this->files );
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function getContent()
	{
		if( $this->content )
			return $this->content;

		$contents = '';
		foreach( $this->getFiles() as $file ) {
			$contents .= file_get_contents( $file );
		}
		return $contents;
	}

}

