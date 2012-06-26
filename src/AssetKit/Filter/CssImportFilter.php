<?php
namespace AssetKit\Filter;


class CssImportFilter
{


    /**
     * @param string $file absolute css file path
     * @param string $baseDir css file baseDir (related css dir path)
     * @return string CSS Content
     */
    public function importCss($file,$baseDir,$rootDir) {
        $content = file_get_contents($file);

        // we should rewrite url( ) paths first, before we import css contents
        $rewrite = new CssRewriteFilter;
        $content = $rewrite->rewrite($content,$baseDir);

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
             * @param string $file    Current CSS file to parse import statement.
             * @param string $baseDir The directory path of current CSS file.
             * @param string $rootDir The root directory path of current .assetkit file
             */
            function($matches) use ($file,$baseDir,$rootDir,$self) {
                // echo "CSS File $file <br/>";
                // var_dump( $matches );

                $path = $matches['url'] ?: $matches['url2'];
                $content = "/* IMPORT FROM $path */" . PHP_EOL;
                if( preg_match( '#^https?://#' , $path ) ) {
                    $content .= file_get_contents( $path );
                }
                else {
                    /* Import recursively */
                    $content .= $self->importCss(
                        $rootDir . DIRECTORY_SEPARATOR . $baseDir . DIRECTORY_SEPARATOR . $path,
                        $baseDir,
                        $rootDir
                    );
                }
                return $content;
        }, $content );

        // echo '<pre>' . $content . '</pre>';
        return $content;
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        // get css files and find @import statement to import related content
        // $assetDir = $collection->asset->getPublicDir();
        $rootDir  = $collection->asset->config->getRoot();

        $contents = '';

        // for rewriting paths
        foreach( $collection->getSourcePaths() as $path ) {
            // css file dir path
            $baseDir = dirname($path);
            $content = $this->importCss( $rootDir . DIRECTORY_SEPARATOR . $path , $baseDir , $rootDir );

            // echo "CSS FILE: " . $path . "<br/>\n\n";
            // echo $content;
            $contents .= $content;
        }
        $collection->setContent( $contents );
    }

}

