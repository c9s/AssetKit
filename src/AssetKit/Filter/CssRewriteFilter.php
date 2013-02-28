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
    const DEBUG = false;


    /**
     * Rewrite css path
     *
     * @param string $content  stylesheet content
     * @param string $dirname  the dirname of stylesheet file
     */
    public function rewrite($content, $fullpath , $dirname, $dirnameUrl, $assetBaseUrl)
    {

        // For path like
        //
        //          url(../images/background.png);
        //
        // In public/assets/test/css/subpath2.css {baseDir} + {assetName} + {path}
        //
        //
        return preg_replace_callback('#
            url\(
                (\'|"|)
                (?<url>.*?)
                \1
            \)
            #xs',
            function($matches) use($fullpath, $dirname, $dirnameUrl , $assetBaseUrl )
            {
                $url = $matches['url'];
                // XXX: dirty, do not rewrite @import css syntax
                if( preg_match('/\.css$/',$url) ) {
                    return $matches[0];
                }

                // if it's already an absolute path, do not rewrite it.
                if( '/' === $url[0] )
                    return $matches[0];


                $origUrl = $url;

                // rewrite with public asset baseurl
                $urlParts = explode('/',$dirnameUrl);
                while (0 === strpos($url, '../') ) {
                    // 2 <= substr_count($dirname, '/')) {
                    array_pop($urlParts);
                    $url = substr($url, 3);
                }
                $dirnameUrl = join('/', $urlParts );
                $url = $dirnameUrl . '/' . $url;

                if(self::DEBUG)
                    echo "Rewriting " , $origUrl , " to " , $url , "\n";

                return str_replace( $matches['url'], $url , $matches[0]);
            }, $content );
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        //  path:  /assets/{asset name}
        $paths = $collection->getFilePaths(); // relative file paths to the asset manifest file.
        $assetBaseUrl = $collection->asset->getBaseUrl();
        $assetSourceDir = $collection->asset->getSourceDir(true);

        $contents = '';
        foreach( $paths as $path ) {
            // absolute path to the file.
            $fullpath = $assetSourceDir . DIRECTORY_SEPARATOR . $path;

            // relative dirname path from asset directory.
            $dirname = dirname($path);

            // url to the directory of the asset.
            $dirnameUrl = $assetBaseUrl . '/' . $dirname;

            $content = file_get_contents($fullpath);
            $content = $this->rewrite( $content, $fullpath, $dirname, $dirnameUrl , $assetBaseUrl);
            $contents .= $content;
        }
        $collection->setContent($contents);
    }

}


