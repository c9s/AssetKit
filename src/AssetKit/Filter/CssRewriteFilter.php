<?php
namespace AssetKit\Filter;

/**
 * Rewrite css url to absolute url (from root path)
 *
 */
class CssRewriteFilter
{
    public $publicRoot;

    public function __construct($publicRoot)
    {
        $this->publicRoot = $publicRoot;
    }

    public function filter($collection)
    {
        $urlBase = $collection->asset->getBaseUrl();
        $files = $collection->getSourcePaths();
        $contents = '';
        foreach( $files as $file ) {
            $content = file_get_contents($file);
            $content = preg_replace_callback('#
                url\( 
                    (\'|"|)
                    (?<url>.*?)
                    \1
                \)
                #xs', $content, function($matches) {

                
                 
                 
                });
        }
        $collection->setContent($contents);
    }

}


