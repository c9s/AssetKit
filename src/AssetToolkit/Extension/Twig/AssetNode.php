<?php
namespace AssetToolkit\Extension\Twig;
use Twig_Node;
use Twig_Compiler;
use Twig_Node_Expression;

class AssetNode extends Twig_Node
{

    public function __construct($name, Twig_Node_Expression $value, $lineno, $tag = null)
    {
        parent::__construct(array('value' => $value), array('name' => $name), $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->subcompile($this->getNode('value'))
            ->raw(";\n")
        ;
    }
}

