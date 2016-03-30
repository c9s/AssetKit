<?php
namespace AssetKit\Extension\Twig;
use Twig_Extension;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetRender;
use AssetKit\AssetCompiler;

class AssetExtension extends Twig_Extension
{
    protected $assetConfig;

    protected $assetLoader;

    protected $render;

    public function __construct(AssetConfig $config, AssetLoader $loader)
    {
        $this->config = $config;
        $this->loader = $loader;
    }


    /**
     * Set AssetKit\AssetConfig
     */
    public function setAssetConfig(AssetConfig $config)
    {
        $this->assetConfig = $config;
    }


    public function getAssetConfig()
    {
        return $this->assetConfig;
    }

    /**
     * Set AssetKit\AssetLoader
     */
    public function setAssetLoader(AssetLoader $loader)
    {
        $this->assetLoader = $loader;
    }

    public function getAssetLoader()
    {
        return $this->assetLoader;
    }


    public function setAssetRender(AssetRender $render)
    {
        $this->render = $render;
    }

    public function getAssetRender()
    {
        if ($this->render) {
            return $this->render;
        }
        return $this->render = new AssetRender($this->assetConfig,$this->assetLoader);
    }

    public function setAssetCompiler(AssetCompiler $compiler)
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
        return 'AssetKit';
    }
}

