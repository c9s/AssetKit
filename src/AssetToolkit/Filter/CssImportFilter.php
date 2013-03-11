<?php
namespace AssetToolkit\Filter;


class CssImportFilter
{
    const DEBUG = 0;

    public function importCss($fullpath, $assetSourceDir, $dirname, $dirnameUrl, $assetBaseUrl)
    {
        if(CssImportFilter::DEBUG)
            echo "Importing from $fullpath\n";
        $content = file_get_contents($fullpath);


        // we should rewrite url( ) paths first, before we import css contents
        $rewrite = new CssRewriteFilter;

        $content = $rewrite->rewrite($content, $dirname, $dirnameUrl);

        $self = $this;

        /**
         * Look for things like:
         *    @import url("jquery.ui.core.css");
         *    @import "jquery.ui.core.css";
         */
        $content = preg_replace_callback('#
            @import
            \s+
                (?:
                    url\(
                        (\'|"|)
                            (?<url>.*?)
                        \1
                    \)
                |
                    ([\'"])
                        (?<url2>.*?)
                    \3
                )
                \s*;
            #xs',

            /**
             * @param string $fullpath Current CSS file to parse import statement.
             * @param string $dirname The directory path of current CSS file.
             */
            function($matches) use ($fullpath, $assetSourceDir, $dirname, $dirnameUrl, $assetBaseUrl, $self) {
                if(CssImportFilter::DEBUG)
                    echo "--> Found {$matches[0]}\n";

                // echo "CSS File $file <br/>";
                // var_dump( $matches );

                $url = $matches['url'] ?: $matches['url2'];


                if(CssImportFilter::DEBUG)
                    echo "--> Importing css from $url\n";

                $content = "/* IMPORT FROM $url */" . PHP_EOL;
                if( preg_match( '#^https?://#' , $url ) ) {
                    // TODO: recursivly import from remote paths
                    $content .= file_get_contents( $url );
                } else {
                    // For css import filter, we need absolute absolute dirname path to import.
                    // For css rewrite filter, we need a relative dirname path to rewrite.
                    $fullDirname = $assetSourceDir . DIRECTORY_SEPARATOR . $dirname;

                    // resolve the relative url
                    $pathParts = explode( DIRECTORY_SEPARATOR, $dirname);
                    $newUrl = $url;
                    while ( 0 === strpos($newUrl, '../') ) {
                        // 2 <= substr_count($dirname, '/'))
                        array_pop($pathParts);
                        $newUrl = substr($newUrl, 3);
                    }
                    $newPath = join( DIRECTORY_SEPARATOR, $pathParts ) . '/' . $newUrl;
                    $newDirname = dirname($newPath);
                    $newDirnameUrl = $assetBaseUrl . '/' . $newDirname;
                    $newFullpath = $assetSourceDir . DIRECTORY_SEPARATOR . $newPath;

                    if(CssImportFilter::DEBUG) {
                        echo $url , " => " , $newPath , "\n";
                    }

                    /* Import recursively */
                    $content .= $self->importCss($newFullpath, $assetSourceDir, $newDirname , $newDirnameUrl, $assetBaseUrl);
                }
                return $content;
        }, $content );

        return $content;
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        // get css files and find @import statement to import related content
        // $assetDir = $collection->asset->getPublicDir();
        $assetSourceDir = $collection->asset->getSourceDir(true);
        $assetBaseUrl = $collection->asset->getBaseUrl();

        $contents = '';

        // for rewriting paths
        foreach( $collection->getFilePaths() as $path ) {
            $fullpath = $assetSourceDir . DIRECTORY_SEPARATOR . $path;

            // the dirname of the file (absolute)
            $dirname = dirname($path);

            // url to the directory of the asset.
            $dirnameUrl = $assetBaseUrl . '/' . $dirname;

            $content = $this->importCss($fullpath, $assetSourceDir, $dirname, $dirnameUrl, $assetBaseUrl);
            $contents .= $content;
        }
        $collection->setContent( $contents );
    }

}

