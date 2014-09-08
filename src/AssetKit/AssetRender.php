<?php
namespace AssetKit;
use Exception;
use RuntimeException;
use AssetKit\AssetCompilerFactory;
use AssetKit\AssetCompiler;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\Exception\UnknownFragmentException;

/**
 * AssetIncluder is the top-level API for including asset files.
 *
 * $render = new AssetRender($config,$loader);
 * $render->render( $manifest );
 */
class AssetRender
{
    public $force = false;

    public $compiler;

    public function __construct(AssetConfig $config, AssetLoader $loader, AssetCompiler $compiler = null)
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
        return $this->compiler = AssetCompilerFactory::create($this->config, $this->loader);
    }




    /**
     * Render assets by target name
     *
     * @param string $target
     */
    public function renderTarget($target)
    {
        // get assets from the target
        $assetNames = $this->loader->getTarget($target);
        if ( ! $assetNames ) {
            throw new RuntimeException("Target $target not found.");
        }
        $assets = $this->loader->loadAssets($assetNames);
        $this->renderAssets($assets, $target);
    }

    /**
     *
     * @param Asset[] $assets
     * @param string $target
     */
    public function renderAssets($assets, $target = '')
    {
        // TODO: Get compiled info by target name from cache or mmap.
        $compiler = $this->getCompiler();
        $out = $compiler->compileAssets($assets, $target, $this->force);
        $this->renderFragments($out);
    }


    /**
     * Render the output fragments to html tags.
     *
     * @param array $outs
     */
    public function renderFragments($outs)
    {
        foreach( $outs as $out ) {
            $this->renderFragment($out);
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
        }
        if ( isset($out['css_url']) ) {
            $this->renderStylesheetTag($out['css_url']);
        }

        if ( isset($out['type']) ) {
            if ( isset($out['url']) ) {
                if ($out['type'] === "stylesheet") {
                    $this->renderStylesheetTag( $out['url'] );
                } elseif ( $out['type'] === "javascript" ) {
                    $this->renderJavascriptTag( $out['url'] );
                } else {
                    throw new UnknownFragmentException("Unknown fragment type: " . $out['type'], $out);
                }
            } else if ( isset($out['content']) ) {
                if($out['type'] === "stylesheet") {
                    echo '<style type="text/css">',  $out['content'] , '</style>' , PHP_EOL;
                } elseif( $out['type'] === "javascript" ) {
                    echo '<script type="text/javascript">', $out['content'] , '</script>' , PHP_EOL;
                } else {
                    throw new UnknownFragmentException("Unknown fragment type: " . $out['type'], $out);
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
        if ($innerContent) {
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

