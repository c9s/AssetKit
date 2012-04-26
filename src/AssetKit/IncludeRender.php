<?php
namespace AssetKit;
use Exception;

/**
 *
 * $render = new IncludeRender;
 * $render->render( $manifest );
 */
class IncludeRender
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
        $html .= '> </script>';
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
        $html .= '/>';
        return $html;
    }


    /**
     * @param array $manifest
     */
    public function render($manifest)
    {
        $html = '';
        // render stylesheets first.
        foreach( $manifest['stylesheets'] as $stylesheet ) {
            /*
            $stylesheet['url'];
            $stylesheet['path'];
            $stylesheet['attrs'];
            */
            $html .= $this->getStylesheetTag( $stylesheet['url'] , $stylesheet['attrs'] );
        }

        foreach( $manifest['javascripts'] as $javascript ) {
            $html .= $this->getJavascriptTag( $javascript['url'] , $javascript['attrs'] );
        }
        return $html;
    }
}


