<?php
namespace AssetToolkit\Extension\Twig;
use Twig_Node;
use Twig_Compiler;
use Twig_Node_Expression;

class AssetNode extends Twig_Node
{

    public function __construct($assetNames, $lineno, $tag = null)
    {
        parent::__construct(array(), array('assetNames' => $assetNames), $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write('echo ')
            ->string( $this->getAttribute('assetNames')[0] )
            ->raw(";\n")
        ;
    }

    /*
    public function __construct($assetNames, $lineno, $tag = null)
    {
        parent::__construct(array('assetNames' => $assetNames), array( ), $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write('// what the fuck');
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->subcompile($this->getNode('value'))
            ->raw(";\n")
        ;
    }
    */
}




