<?php
namespace AssetKit;
use Exception;

/**
 * AssetIncluder is the top-level API for including asset files.
 *
 * $render = new AssetRender($config,$loader);
 * $render->setEnvironment(  )
 * $render->render( $manifest );
 */
class AssetRender
{
    const PRODUCTION = 1;
    const DEVELOPMENT = 2;

    /**
     * ->setEnvironment( AssetIncluder::PRODUCTION );
     * ->setEnvironment( AssetIncluder::DEVELOPMENT );
     */
    public $environment = self::DEVELOPMENT;

    public function __construct($config,$loader)
    {
        $this->config = $config;
        $this->loader = $loader;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    public function getCompiler()
    {
        return new AssetCompiler($this->config,$this->loader);
    }

    public function renderAssets($assets) 
    {
        $compiler = $this->getCompiler();
        if($this->environment === self::DEVELOPMENT ) {
            $outs = $compiler->compileAssetsForDevelopment($assets);

            // render output fragments
            foreach( $outs as $out ) {

            }
        }
        elseif ($this->environment === self::PRODUCTION ) {
            $out = $compiler->compileAssetsForProduction($assets);
        }
    }

    /**
     * @param string $url
     * @param array $attributes
     */
    public function getJavascriptTag($url,$attributes = array())
    {
        $html = '<script type="text/javascript" ';
        $html .= ' src="' . $url . '" ';
        foreach( $attributes as $name => $value ) {
            $html .= ' ' . $name . '="' . $value . '"'; 
        }
        $html .= '> </script>' . "\n";
        return $html;
    }

    /**
     * @param string $url
     * @param array $attributes
     */
    public function getStylesheetTag($url,$attributes = array())
    {
        $html = '<link rel="stylesheet" type="text/css" ';
        $html .= ' href="' . $url . '"' ;
        foreach( $attributes as $name => $value ) {
            $html .= ' ' . $name . '="' . $value . '"'; 
        }
        $html .= '/>' . "\n";
        return $html;
    }
}

