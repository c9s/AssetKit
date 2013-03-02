<?php
namespace AssetToolkit\Extension\Twig;
use Twig_TokenParser;
use Twig_Token;

class AssetTokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $name = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
        $this->parser->getStream()->expect(Twig_Token::OPERATOR_TYPE, '=');
        $value = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new AssetNode($name, $value, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'asset';
    }
}

