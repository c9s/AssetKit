<?php

class CssMinTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $css =<<<CSS

div.list {
    color: blue;
    background: #ccc;
}

CSS;
        require_once 'src/AssetKit/CssMin.php';
        $return = CssMin::minify( $css );
        is('div.list{color:blue;background:#ccc}',$return);
    }
}

