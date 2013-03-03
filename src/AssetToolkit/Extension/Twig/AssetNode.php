<?php
namespace AssetToolkit\Extension\Twig;
use Twig_Node;
use Twig_Compiler;
use Twig_Node_Expression;

class AssetNode extends Twig_Node
{

    public function __construct($attributes, $lineno, $tag = null)
    {
        parent::__construct(array(), $attributes, $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $assetNames = $this->getAttribute('assetNames');
        $target     = $this->getAttribute('target') ?: '';
        $compiler->raw("\$extension = \$this->getEnvironment()->getExtension('AssetToolkit');\n");
        $compiler->raw("\$assetloader = \$extension->getAssetLoader();\n");
        $compiler->raw("\$assetrender = \$extension->getAssetRender();\n");
        $compiler->raw("\$assets = array();\n");
        foreach($assetNames as $assetName) {
            $compiler->raw("\$assets[] = \$assetloader->load('$assetName');");
        }
        $compiler->raw("\$assetrender->renderAssets(\$assets,'$target');");
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




