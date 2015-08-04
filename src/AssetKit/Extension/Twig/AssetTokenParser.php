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
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {

            /*
            ;
            var_dump( $value ); 
            */
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
            $token = $stream->expect(Twig_Token::STRING_TYPE);
            $attributes['target'] = new Twig_Node_Expression_Constant($token->getValue(), $token->getLine());

        } else if ($stream->test(Twig_Token::NAME_TYPE, 'config')) {
            // debug=true
            $stream->next();
            $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
            $attributes['debug'] =
                'true' == $stream->expect(Twig_Token::NAME_TYPE, array('true', 'false'))->getValue();
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
        return 'assets';
    }
}

