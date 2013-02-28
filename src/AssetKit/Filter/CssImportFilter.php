<?php
namespace AssetKit\Filter;


class CssImportFilter
{
    const DEBUG = 0;

    public function importCss($fullpath, $dirname,$rootDir) 
    {
        if(self::DEBUG)
            echo "Importing from $fullpath\n";
        $content = file_get_contents($fullpath);

        // we should rewrite url( ) paths first, before we import css contents
        // $rewrite = new CssRewriteFilter;
        // $content = $rewrite->rewrite($content,$dirname);

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
             * @param string $rootDir The root directory path of current .assetkit file
             */
            function($matches) use ($fullpath, $dirname, $rootDir, $self) {
                if(self::DEBUG)
                    echo "--> Found {$matches[0]}\n";

                // echo "CSS File $file <br/>";
                // var_dump( $matches );

                $url = $matches['url'] ?: $matches['url2'];


                if(self::DEBUG)
                    echo "--> Importing css from $url\n";

                $content = "/* IMPORT FROM $url */" . PHP_EOL;
                if( preg_match( '#^https?://#' , $url ) ) {
                    // TODO: recursivly import from remote paths
                    $content .= file_get_contents( $url );
                }
                else {

                    // resolve the relative url
                    $pathParts = explode( DIRECTORY_SEPARATOR ,$dirname);
                    $newUrl = $url;
                    while ( 0 === strpos($newUrl, '../') ) {
                        // 2 <= substr_count($dirname, '/')) {
                        array_pop($pathParts);
                        $newUrl = substr($newUrl, 3);
                    }
                    $newFullpath = join( DIRECTORY_SEPARATOR, $pathParts ) . '/' . $newUrl;
                    $newDirname = dirname($newFullpath);

                    if(self::DEBUG)
                        echo $url , " => " , $newFullpath , "\n";

                    /* Import recursively */
                    $content .= $self->importCss( $newFullpath, $newDirname, $rootDir );
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
        $rootDir  = $collection->asset->config->getRoot();
        $sourceDir = $collection->asset->getSourceDir(true);
        $contents = '';

        // for rewriting paths
        foreach( $collection->getFilePaths() as $file ) {
            $fullpath = $sourceDir . DIRECTORY_SEPARATOR . $file;

            if(self::DEBUG)
                echo "Processing $fullpath\n";

            // the dirname of the file (absolute)
            $dirname = dirname($fullpath);
            $content = $this->importCss($fullpath, $dirname, $rootDir);
            $contents .= $content;
        }
        $collection->setContent( $contents );
    }

}

