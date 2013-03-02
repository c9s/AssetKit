<?php
namespace AssetToolkit\Extension\Twig;
use Twig_TokenParser;
use Twig_Token;

class AssetTokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $attributes = array(
            'assetNames' => array(),
            'target' => null,
        );
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            // take asset names
            while ($stream->test(Twig_Token::STRING_TYPE)) {
                $attributes['assetNames'][] = $stream->next()->getValue();

                if ( $stream->test(Twig_Token::PUNCTUATION_TYPE, ',') ) {
                    $stream->next();
                } else {
                    break;
                }
            }

            if ($stream->test(Twig_Token::NAME_TYPE, 'as')) {
                $stream->next();
                $attributes['target'] = $stream->expect(Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'config')) {
                // debug=true
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['debug'] =
                    'true' == $stream->expect(Twig_Token::NAME_TYPE, array('true', 'false'))->getValue();
            }

        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new AssetNode($attributes, $lineno, $this->getTag());

        /*
        if ($stream->test(Twig_Token::NAME_TYPE, 'as')) {
            $stream->next();
            $target = $stream->expect(Twig_Token::STRING_TYPE)->getValue();
        } else {

        }
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
        }
        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
        $value = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new AssetNode($assetNames, $lineno, $this->getTag());
        */

        /*
        $lineno = $token->getLine();
        $name = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
        $this->parser->getStream()->expect(Twig_Token::OPERATOR_TYPE, '=');
        $value = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        return new AssetNode($name, $value, $lineno, $this->getTag());
         */
    }

    public function getTag()
    {
        //return 'seta';
        return 'assets';
    }
}

