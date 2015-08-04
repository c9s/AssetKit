<?php
namespace AssetKit\Extension\Twig;
use Twig_TokenParser;
use Twig_Token;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_Array;
use Twig_Node_Expression_Name;

class AssetTokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $attributes = array(
            'assets' => array(),
            'target' => null,
        );

        // take asset names
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE) && !$stream->test(Twig_Token::NAME_TYPE, 'as')) {

            if ($stream->test(Twig_Token::STRING_TYPE)) {

                $token = $stream->next();

                $strNode = new Twig_Node_Expression_Constant($token->getValue(), $token->getLine());

                $attributes['assets'][] = $strNode;

                if ($stream->test(Twig_Token::PUNCTUATION_TYPE, ',')) {
                    $stream->next();
                } else {
                    break;
                }
            } else if($stream->test(Twig_Token::NAME_TYPE, 'as')) {
                break;
            } else if ($expr = $this->parser->getExpressionParser()->parsePrimaryExpression()) {

                $attributes['assets'][] = $expr;

            } else if ($stream->test(Twig_Token::BLOCK_END_TYPE)) {

                break;

            } else {

                break;

            }
        }

        // skip "as" keyword
        if ($stream->test(Twig_Token::NAME_TYPE, 'as')) {
            $stream->next();
            $targetVar = $this->parser->getExpressionParser()->parseExpression();
            $attributes['target'] = $targetVar;
        }

        if ($stream->test(Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();
            $configVar = $this->parser->getExpressionParser()->parseExpression();
            $attributes['config'] = $configVar;
        }


        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new AssetNode($attributes, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'assets';
    }
}

