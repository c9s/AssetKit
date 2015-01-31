<?php
namespace AssetKit;
use Exception;
use IteratorAggregate;
use ArrayAccess;

class Collection
    implements IteratorAggregate, ArrayAccess
{
    const FileTypeFile   = 1;

    const FileTypeJs     = 2;

    const FileTypeCss    = 3;

    const FileTypeSass   = 4;

    const FileTypeScss   = 5;

    const FileTypeCoffee = 6;


    public $filters = array();

    public $compressors = array();

    public $files = array();

    /**
     * @var the collection ID
     */
    public $id;

    /**
     * @var string path Asset source directory
     */
    public $sourceDir;


    /**
     * @var boolean content type for <style> or rel="stylesheet"
     */
    public $isStylesheet;

    /**
     * @var boolean content type for <script>
     */
    public $isScript;

    /**
     * file content cache (content is from the getContent method)
     */
    public $content;


    public $stash = array();


    /**
     * file chunks with metadata
     */
    public $chunks;


    /**
     * @var int  FileTypeFile, FileTypeJs, FileTypeCoffee, FileTypeCss, FileTypeSass, FileTypeScss, FileTypeCss
     */
    public $filetype;


    // attributes for assets rendering
    public $attributes = array();

    // cache
    private $_lastmtime = 0;

    public function __construct(array $stash = array()) {
        $this->stash = $stash;
    }


    /**
     * Return source path (with relative or absolute path)
     *
     * @param bool $absolute Should return absolute or relative.
     * @return string
     */
    public function getSourcePaths()
    {
        return \futil_paths_prepend($this->files, $this->sourceDir);
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
    public function setChunks(array $chunks)
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
    public function squashChunks(array $chunks)
    {
        $content = '';
        foreach( $chunks as $c ) {
            $content .= $c['content'];
        }
        return $content;
    }


    public function setContent($content)
    {
        if ($content === NULL || $content === FALSE) {
            throw new EmptyContentException("The assigned content is empty.");
        }
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


    public function initContentType(array & $stash) {
        // for normal static files
        if( isset($stash['file']) ) {
            $this->filetype = Collection::FileTypeFile;
            return 'file';
        } elseif (isset($stash['js'])) {
            $this->filetype     = Collection::FileTypeJs;
            $this->isScript = true;
            return 'js';
        } elseif (isset($stash['javascript'])) {
            $this->filetype     = Collection::FileTypeJs;
            $this->isScript = true;
            return 'javascript';
        } elseif (isset($stash['coffeescript'])) {
            $this->filetype       = Collection::FileTypeCoffee;
            $this->isScript = true;
            return 'coffeescript';
        } elseif (isset($stash['coffee'])) {
            $this->filetype       = Collection::FileTypeCoffee;
            $this->isScript = true;
            return 'coffee';
        } elseif (isset($stash['css'])) {
            $this->filetype     = Collection::FileTypeCss;
            $this->isStylesheet = true;
            return 'css';
        } elseif (isset($stash['sass'])) {
            $this->filetype     = Collection::FileTypeSass;
            $this->isStylesheet = true;
            return 'sass';
        } elseif (isset($stash['scss'])) {
            $this->filetype     = Collection::FileTypeScss;
            $this->isStylesheet = true;
            return 'scss';
        } elseif (isset($stash['stylesheet']) ) {
            $this->filetype     = Collection::FileTypeCss;
            $this->isStylesheet = true;
            return 'stylesheet';
        } else {
            var_dump( $stash );
            throw new Exception('Unknown collection file type.');
        }
    }

    public function __set_state($array) {
        $c = new self;
        $c->filters = $array['filters'];
        $c->compressors = $array['compressors'];
        $c->files = $array['files'];
        $c->sourceDir = $array['sourceDir'];
        $c->filetype = $array['filetype'];
        $c->isStylesheet = $array['isStylesheet'];
        $c->isScript = $array['isScript'];
        return $c;
    }


    public function offsetSet($name,$value)
    {
        $this->stash[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->stash[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->stash[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->stash[$name]);
    }

    public function getStash()
    {
        return $this->stash;
    }
}

