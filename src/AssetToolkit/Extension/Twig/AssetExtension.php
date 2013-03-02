<?php
namespace AssetToolkit\Extension\Twig;
use Twig_Extension;

class AssetExtension extends Twig_Extension
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array( new AssetTokenParser() );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'AssetToolkit';
    }
}

