<?php
namespace AssetKit;

class AssetCompiler
{
    const PRODUCTION = 1;
    const DEVELOPMENT = 2;

    /**
     * Can be AssetCompiler::PRODUCTION or AssetCompiler::DEVELOPMENT
     *
     * $compiler->setEnvironment( AssetCompiler::PRODUCTION );
     * $compiler->setEnvironment( AssetCompiler::DEVELOPMENT );
     */
    public $environment = self::DEVELOPMENT;

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    /**
     * Method for compiling one asset
     *
     *
     * For example:
     *
     * baseDir: public/assets
     * baseUrl: /assets
     *
     * And the asset directory:
     *
     * assets/jquery
     * assets/jquery/manifest.yml
     * assets/jquery/jquery-1.8.2.js
     *
     * Will be compiled into:
     *
     * public/assets/jquery/jquery.min.js
     *
     */
    public function compile($asset) 
    {
        $data = $this->squash($asset);

        // get the absolute path of install dir.
        $installDir = $asset->getInstallDir(true);
        $name = $asset->name;

        $jsFile = $installDir . DIRECTORY_SEPARATOR . $name . '.js';
        $cssFile = $installDir . DIRECTORY_SEPARATOR . $name . '.css';

        if($data['js'])
            file_put_contents( $jsFile, $data['js'] );
        if($data['css'])
            file_put_contents( $cssFile, $data['css'] );

        return array(
            'js' => $jsFile,
            'css' => $cssFile,
        );
    }



    /**
     * Compile multiple assets.
     */
    public function compileAssets($assets) 
    {

    }


    /**
     * Squash asset contents,
     * run through filters, compressors ...
     *
     * @param  AssetKit\Asset $asset
     * @return array [ css: string, js: string ]
     */
    public function squash($asset)
    {
        $js = '';
        $css = '';
        $collections = $asset->getCollections();

        foreach( $collections as $collection ) {

            // skip unknown types
            if( ! $collection->isJavascript && ! $collection->isStylesheet )
                continue;

            // if we are in development mode, we don't need to compress them all,
            // we just filter them
            if( $this->environment === self::PRODUCTION 
                    && $this->enableCompressor ) 
            {
                // for stylesheets, before compress it, we should import the css contents
                if( $collection->isStylesheet ) {
                    $import = new Filter\CssImportFilter;
                    $import->filter( $collection );
                }
                elseif( $collection->isCoffeescript ) {
                    $coffee = new Filter\CoffeeScriptFilter;
                    $coffee->filter( $collection );
                }

                if( $collection->getFilters() ) {
                    $this->runCollectionFilters( $collection );
                }
                $this->runCollectionCompressors($collection);
            }
            else {
                // for development mode, simply run filters
                $this->runCollectionFilters( $collection );
            }

            if( $collection->isJavascript ) {
                $js .= $collection->getContent();
            } 
            elseif( $collection->isStylesheet ) {
                $css .= $collection->getContent();
            }
        }
        return array(
            'js' => $js,
            'css' => $css,
        );
    }

    public function runCollectionFilters($collection)
    {
        if( empty($collection->filters) )
            return;

        if( $collection->hasFilter('no') )
            return;

        foreach( $collection->filters as $n ) {
            if( $filter = $this->getFilter( $n ) ) {
                $filter->filter($collection);
            }
            else {
                throw new Exception("filter $n not found.");
            }
        }
    }

    /**
     * Run compressors at the end
     *
     *
     */
    public function runCollectionCompressors($collection)
    {
        // if custom compresor is not define, use default compressors
        if( empty($collection->compressors) ) {
            if( $collection->isJavascript || $collection->isCoffeescript ) {
                $jsmin = new Compressor\JsMinCompressor;
                $jsmin->compress($collection);
            }
            elseif( $collection->isStylesheet ) {
                $cssmin = new Compressor\CssMinCompressor;
                $cssmin->compress($collection);
            }
        }
        else {
            if( $collection->hasCompressor('no') )
                return;

            foreach( $collection->compressors as $n ) {
                if( $compressor = $this->getCompressor( $n ) ) {
                    $compressor->compress($collection);
                }
                else {
                    throw new Exception("compressor $n not found.");
                }
            }
        }
    }

}



