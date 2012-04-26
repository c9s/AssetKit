<?php
namespace AssetKit\Filter;

/**
 * Rewrite css url to absolute url (from root path)
 *
 * 1. read css files from asset source dir
 * 2. parse url(s) 
 * 3. rewrite assets
 *
 */
class CssRewriteFilter
{
    public function filter($collection)
    {

        //  path:  /assets/{asset name}
        $urlBase = $collection->asset->getBaseUrl();
        $paths = $collection->getSourcePaths();
        $contents = '';
        foreach( $paths as $path ) {
            $dir = dirname($path);
            $content = file_get_contents($path);
            $content = preg_replace_callback('#
                url\( 
                    (\'|"|)
                    (?<url>.*?)
                    \1
                \)
                #xs', function($matches) use($urlBase,$dir) {
                    $url = $matches['url'];
                    // XXX: dirty, do not rewrite @import css syntax
                    if( preg_match('/\.css$/',$url) ) {
                        return $matches[0];
                    }

                    if( '/' === $url[0] )
                        return $matches[0];

                    // rewrite with public asset baseurl
                    while (0 === strpos($url, '../') && 2 <= substr_count($dir, '/')) {
                        $dir = substr($dir, 0, strrpos(rtrim($dir, '/'), '/') + 1);
                        $url = substr($url, 3);
                    }
                    // echo "Replacing " , $matches['url'] , " to " , '/' . $dir . '/' . $url , "\n";
                    return str_replace( $matches['url'], '/' . $dir . '/' . $url , $matches[0]);
                }, $content );
            $contents .= $content;
        }
        $collection->setContent($contents);
    }

}


