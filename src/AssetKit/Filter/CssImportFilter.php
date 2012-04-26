<?php
namespace AssetKit\Filter;

class CssImportFilter
{

    public function filter($collection) 
    {
        // get css files and find @import statement to import related content
        $assetDir = $collection->asset->getPublicDir();
        foreach( $collection->getSourcePaths() as $path ) {
            $dir = dirname($path);
            $content = file_get_contents( $path );


            /**
             * Looking for things like:
             *
             *    @import url("jquery.ui.core.css");
             *
             */
            preg_replace_callback('#
                @import 
                \s+
                url\(   
                    (\'|"|)
                    (?<url>.*?)
                    (\'|"|)
                \);
                #x', 
                function($matches) use ($path,$dir) {
                    var_dump( $matches ); 

                    return '';
            }, $content );
        }
    }

}

