<?php
namespace AssetToolkit;
use Exception;
use RuntimeException;

/**
 * AssetIncluder is the top-level API for including asset files.
 *
 * $render = new AssetRender($config,$loader);
 * $render->setEnvironment(  )
 * $render->render( $manifest );
 */
class AssetRender
{

    public $force = false;

    public $compiler;

    public function __construct($config,$loader, $compiler = null)
    {
        $this->config = $config;
        $this->loader = $loader;
        if ( $compiler ) {
            $this->compiler = $compiler;
        }
    }


    public function force() 
    {
        $this->force = true;
    }


    public function getCompiler()
    {
        if ($this->compiler) {
            return $this->compiler;
        }

        // default compiler
        $this->compiler = new AssetCompiler($this->config,$this->loader);
        $this->compiler->registerDefaultCompressors();
        $this->compiler->registerDefaultFilters();
        return $this->compiler;
    }


    public function renderLoadedAssets($target = '') 
    {
        $assetMap = $this->loader->all();
        $assets = array_values($assetMap);
        $this->renderAssets($assets, $target);
    }

    public function renderTarget($target)
    {
        // get assets from the target
        $assetNames = $this->config->getTarget($target);
        if ( ! $assetNames ) {
            throw new RuntimeException("Target $target not found.");
        }
        $assets = $this->loader->loadAssets($assetNames);
        return $this->renderAssets($assets, $target);
    }

    public function renderAssets($assets, $target = '')
    {
        $compiler = $this->getCompiler();
        if($this->config->environment === AssetConfig::DEVELOPMENT ) {
            $outs = $compiler->compileAssetsForDevelopment($assets, $target);
            $this->renderFragments($outs);
        }
        elseif ($this->config->environment === AssetConfig::PRODUCTION ) {
            $out = $compiler->compileAssetsForProduction($assets, $target, $this->force);
            $this->renderFragment($out);
        } else {
            throw new Exception("Unknown environment type.");
        }
    }



    /**
     * Render the output fragments to html tags.
     *
     * @param array $outs
     */
    public function renderFragments($outs)
    {
        foreach( $outs as $out ) {
            echo $this->renderFragment($out);
        }
    }


    /**
     * Render one single fragment.
     *
     * @param array $out
     */
    public function renderFragment($out)
    {
        // check for css_url and js_url
        if ( isset($out['js_url']) ) {
            $this->renderJavascriptTag($out['js_url']);
        } if ( isset($out['css_url']) ) {
            $this->renderStylesheetTag($out['css_url']);
        } elseif ( isset($out['type']) ) {

            if ( isset($out['url']) ) {
                if ($out['type'] === "stylesheet") {
                    $this->renderStylesheetTag( $out['url'] );
                } elseif ( $out['type'] === "javascript" ) {
                    $this->renderJavascriptTag( $out['url'] );
                } else {
                    throw new Exception("Unknown fragment.");
                }
            } else if ( isset($out['content']) ) {
                if($out['type'] === "stylesheet") {
                    echo '<style type="text/css">',  $out['content'] , '</style>' , PHP_EOL;
                } elseif( $out['type'] === "javascript" ) {
                    echo '<script type="text/javascript">', $out['content'] , '</script>' , PHP_EOL;
                } else {
                    throw new Exception("Unknown fragment.");
                }
            }
        }
    }



    /**
     * @param string $url
     * @param array $attributes
     */
    public function renderJavascriptTag($url, $innerContent = '' ,$attributes = array())
    {
        echo '<script type="text/javascript" src="' . $url . '"';
        foreach( $attributes as $name => $value ) {
            echo ' ' , $name , '="' , $value , '"';
        }
        echo '>';

        if($innerContent) {
            echo $innerContent;
        }
        echo '</script>' , PHP_EOL;
    }

    /**
     * @param string $url
     * @param array $attributes
     */
    public function renderStylesheetTag($url,$attributes = array())
    {
        // <link rel="stylesheet" href="http://static.ak.fbcdn.net/rsrc.php/v2/yJ/r/S-EheTP3T8X.css"/>
        echo '<link rel="stylesheet" type="text/css"';
        echo ' href="' . $url . '"';
        foreach( $attributes as $name => $value ) {
            echo ' ' . $name . '="' . $value . '"';
        }
        echo '/>' , PHP_EOL;
    }
}

