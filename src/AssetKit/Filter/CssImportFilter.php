<?php
namespace AssetKit\Filter;

class CssImportFilter
{

    public function filter($collection) 
    {
        // get css files and find @import statement to import related content
        $assetDir = $collection->asset->getPublicDir();
        foreach( $collection->getPublicPaths() as $path ) {
            $content = file_get_contents( $path );

            preg_replace_callback('#url\(([^)]+)\)#' , function($matches) {
                list($orig,$url) = $matches;

                return '';
            }, $content );

#           preg_replace_callback('#@import\s+"[^"]*"#', function($matches) { 
#               var_dump( $matches ); 
#           });
        }
    }

}

