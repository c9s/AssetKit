<?php
namespace AssetToolkit\Extension\Twig;
use Twig_Extension;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetRender;
use AssetToolkit\AssetCompiler;

class AssetExtension extends Twig_Extension
{
    protected $assetConfig;
    protected $assetLoader;
    protected $render;

    /**
     * Set AssetToolkit\AssetConfig
     */
    public function setAssetConfig($config)
    {
        $this->assetConfig = $config;
    }


    public function getAssetConfig()
    {
        return $this->assetConfig;
    }

    /**
     * Set AssetToolkit\AssetLoader
     */
    public function setAssetLoader($loader)
    {
        $this->assetLoader = $loader;
    }

    public function getAssetLoader()
    {
        return $this->assetLoader;
    }


    public function setAssetRender($render)
    {
        $this->render = $render;
    }

    public function getAssetRender()
    {
        if($this->render)
            return $this->render;
        return $this->render = new AssetRender($this->assetConfig,$this->assetLoader);
    }

    public function setAssetCompiler($compiler)
    {
        $this->getAssetRender()->setCompiler($compiler);
    }

    public function getAssetCompiler()
    {
        return $this->getAssetRender()->getCompiler();
    }


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

