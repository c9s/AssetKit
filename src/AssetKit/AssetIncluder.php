<?php
namespace AssetKit;
use Exception;

/**
 * AssetIncluder is the top-level API for including asset files.
 *
 * $render = new AssetInclude;
 * $render->render( $manifest );
 */
class AssetIncluder
{


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


    /**
     * @param array $manifest
     */
    public function render($manifest)
    {
        $html = '';
        // render stylesheets first.
        foreach( $manifest['css_url'] as $stylesheet ) {
            /*
            $stylesheet['url'];
            $stylesheet['path'];
            $stylesheet['attrs'];
            */
            $html .= $this->getStylesheetTag( $stylesheet['url'] , $stylesheet['attrs'] );
        }

        foreach( $manifest['js_url'] as $javascript ) {
            $html .= $this->getJavascriptTag( $javascript['url'] , $javascript['attrs'] );
        }
        return $html;
    }
}


