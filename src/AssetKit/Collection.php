<?php
namespace AssetKit;
use Exception;
use IteratorAggregate;

class Collection
    implements IteratorAggregate
{

    public $filters = array();

    public $compressors = array();

    public $files = array();

    // public $asset;

    /**
     * @param path Asset source directory
     */
    public $sourceDir;

    public $isJavascript;

    public $isStylesheet;

    public $isCoffeescript;


    /**
     * file content cache (content is from the getContent method)
     */
    public $content;


    /**
     * file chunks with metadata
     */
    public $chunks;

    public $filetype;

    // attributes for assets rendering
    public $attributes = array();

    // cache
    private $_lastmtime = 0;

    const FILETYPE_FILE   = 1;
    const FILETYPE_JS     = 2;
    const FILETYPE_CSS    = 3;
    const FILETYPE_SASS   = 4;
    const FILETYPE_SCSS   = 5;
    const FILETYPE_COFFEE = 6;

    /**
     * Return source path (with relative or absolute path)
     *
     * @param bool $absolute Should return absolute or relative.
     * @return string
     */
    public function getSourcePaths()
    {
        return \futil_paths_prepend($this->files,$this->sourceDir);
    }


    /**
     * Return fullpath of files
     *
     * @return string[] fullpaths.
     */
    public function getFullpaths()
    {
        return \futil_paths_prepend($this->files, $this->sourceDir);
    }

    /**
     * @return array return the collection file list
     */
    public function getFilePaths()
    {
        return $this->files;
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
        if ( $this->_lastmtime ) {
            return $this->_lastmtime;
        }
        if ( ! empty($this->files) ) {
            return $this->_lastmtime = \futil_lastmtime($this->getFullpaths());
        }
        return 0;
    }



    /**
     * Set content chunks with metadata.
     *
     * @param array $chunks
     */
    public function setChunks($chunks)
    {
        $this->chunks = $chunks;
    }

    /**
     * Returns content chunks with metadata.
     *
     * @return [content=>,path=>,fullpath=>][]
     */
    public function getChunks()
    {
        if ( $this->chunks ) {
            return $this->chunks;
        }

        foreach( $this->files as $file ) {
            $fullpath = $this->sourceDir . DIRECTORY_SEPARATOR . $file;

            if ( ($out = file_get_contents( $fullpath )) !== false ) {
                $this->chunks[] = array(
                    'content' => $out,
                    'path'    => $file,
                    'fullpath' => $fullpath,
                );
            } else {
                throw new Exception("Asset collection: Can not read file $fullpath");
            }
        }
        return $this->chunks;
    }



    /**
     * Squash chunks into a string.
     *
     * @return string
     */
    public function squashChunks($chunks)
    {
        $content = '';
        foreach( $chunks as $c ) {
            $content .= $c['content'];
        }
        return $content;
    }


    public function setContent($content)
    {
        // Warning: calling setContent to chunks might lose metadata.
        $this->chunks = array(array(
            'content' => $content, 
            'fullpath' => '',
            'path' => '',
        )); 
    }

    public function getContent()
    {
        $chunks = $this->getChunks();
        return $this->squashChunks($chunks);
    }




    public function getIterator()
    {
        return new ArrayIterator($this->getSourcePaths());
    }


    /**
     * Check if collection files are out of date.
     */
    public function isOutOfDate($fromTime)
    {
        return $this->getLastModifiedTime() > $fromTime;
    }
}

