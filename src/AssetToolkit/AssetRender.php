<?php
namespace AssetToolkit;
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

    public $force = false;

    public $compiler;

    public function __construct($config,$loader)
    {
        $this->config = $config;
        $this->loader = $loader;
    }


    public function force() 
    {
        $this->force = true;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    public function getCompiler()
    {
        if($this->compiler)
            return $this->compiler;

        // default compiler
        $this->compiler = new AssetCompiler($this->config,$this->loader);
        $this->compiler->registerDefaultCompressors();
        $this->compiler->registerDefaultFilters();
        return $this->compiler;
    }

    public function renderAssets($target, $assets)
    {
        $compiler = $this->getCompiler();
        if($this->environment === self::DEVELOPMENT ) {
            $outs = $compiler->compileAssetsForDevelopment($assets);
            $this->renderFragments($outs);
        }
        elseif ($this->environment === self::PRODUCTION ) {
            $out = $compiler->compileAssetsForProduction($target, $assets, $this->force);
            $this->renderFragment($out);
        } else {
            throw new Exception("Unknown environment type.");
        }
    }

    public function renderFragments($outs)
    {
        foreach( $outs as $out ) {
            echo $this->renderFragment($out);
        }
    }


    public function renderFragment($out)
    {
        // check for css_url and js_url
        if( isset($out['js_url']) ) {
            echo $this->getJavascriptTag($out['js_url']);
        }
        if( isset($out['css_url']) ) {
            echo $this->getStylesheetTag($out['css_url']);
        }

        if( isset($out['type']) ) {
            if($out['type'] === "stylesheet") {
                echo '<style type="text/stylesheet"';
                if(isset($out['content'])) {
                    echo '>' . $out['content'];
                } elseif(isset($out['url']) ) {
                    echo ' src="'. $out['url'] . '">';
                }
                echo '</style>';
            } elseif( $out['type'] === "javascript" ) {
                echo '<script type="text/javascript"';
                if(isset($out['content'])) {
                    echo '>' . $out['content'];
                } elseif(isset($out['url']) ) {
                    echo ' src="'. $out['url'] . '">';
                }
                echo '</script>';
            }
        }
    }



    /**
     * @param string $url
     * @param array $attributes
     */
    public function getJavascriptTag($url, $innerContent = '' ,$attributes = array())
    {
        $html = '<script type="text/javascript" ';
        $html .= ' src="' . $url . '" ';
        foreach( $attributes as $name => $value ) {
            $html .= ' ' . $name . '="' . $value . '"'; 
        }
        $html .= '>';
        if($innerContent) {
            $html .= $innerContent;
        }
        $html .= '</script>' . "\n";
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

