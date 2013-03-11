<?php
namespace AssetToolkit\Filter;

/**
 * Rewrite css url to absolute url (from root path)
 *
 * 1. read css files from asset source dir.
 * 2. parse url(s).
 * 3. rewrite assets.
 * 4. set content.
 *
 */
class CssRewriteFilter
{
    const DEBUG = false;


    /**
     * Rewrite css url paths from css content.
     *
     * This method parses css content ($content), finds url(..) by patterns,
     * Resolve the relative URL to the absolute URL based on the dirnameUrl 
     * we've provided, e.g.,
     *
     * In the css file assets/product/css/product.css.
     *
     * If the below css rule is found:
     *
     *     background: url(../images/bg.png);
     *
     * Then we resolve the "../images/bg.png" path to got the parent 
     * url path:
     *
     *    /product/css => /product
     *
     * Then we concat the url path with the base url that we just found:
     *
     *    /product       + "/images/bg.png"
     *
     * @param string $content  stylesheet content.
     * @param string $dirnameUrl the url of the diretory
     */
    public function rewrite($content, $dirnameUrl)
    {
        return preg_replace_callback('#
            url\(
                (\'|"|)
                (?<url>.*?)
                \1
            \)
            #xs',
            function($matches) use($dirnameUrl)
            {
                $url = $matches['url'];

                // do not rewrite @import css syntax
                if ( preg_match('/\.css$/',$url) ) {
                    return $matches[0];
                }

                // do not rewrite
                // if it's already an absolute path.
                if ( '/' === $url[0] ) {
                    return $matches[0];
                }


                $origUrl = $url;

                // rewrite with public asset baseurl
                $urlParts = explode('/',$dirnameUrl);
                while (0 === strpos($url, '../') ) {
                    array_pop($urlParts);
                    $url = substr($url, 3);
                }
                $dirnameUrl = join('/', $urlParts );
                $url = $dirnameUrl . '/' . $url;

                if (CssRewriteFilter::DEBUG) {
                    echo "Rewriting " , $origUrl , " to " , $url , "\n";
                }

                // replace the found string with the new absolute url.
                return str_replace( $matches['url'], $url , $matches[0]);
            }, $content );
    }



    /**
     * Note that in this method, we didn't use the `getContent` method 
     * to retrieve file contents, because we need the base dir path 
     * to resolve paths.
     *
     * @param Collection $collection
     */
    public function filter($collection)
    {
        if ( ! $collection->isStylesheet )
            return;

        //  path:  /assets/{asset name}
        $paths = $collection->getFilePaths(); // relative file paths to the asset manifest file.
        $assetBaseUrl = $collection->asset->getBaseUrl();
        $assetSourceDir = $collection->asset->getSourceDir(true);

        $contents = '';
        foreach( $paths as $path ) {
            // absolute path to the file.
            $fullpath = $assetSourceDir . DIRECTORY_SEPARATOR . $path;

            $contents .= $this->rewrite( 
                file_get_contents($fullpath), 

                // url to the directory of the asset.
                $assetBaseUrl . '/' . dirname($path);
            );
        }
        $collection->setContent($contents);
    }

}


