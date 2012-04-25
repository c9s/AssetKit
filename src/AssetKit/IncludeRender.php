<?php
namespace AssetKit;

class IncludeRender
{
    public function getJavascriptTag($url,$attributes = array())
    {
        $html = '<script type="text/javascript" ';
        $html .= ' src="' . $url . '" ';
        foreach( $attributes as $name => $value ) {
            $html .= ' ' . $name . '="' . $value . '"'; 
        }
        $html .= '/>';
        return $html;
    }

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

    public function renderManifest($manifest)
    {
        foreach( $manifest['stylesheet'] as $stylesheet ) {

        }

    }
}


