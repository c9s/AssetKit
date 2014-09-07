<?php
namespace AssetKit;

class AssetProductionCompiler extends AssetCompiler
{
    /**
     * Compile multiple assets into the target path.
     *
     * For example, compiling:
     *
     *    - jquery
     *    - jquery-ui
     *    - blueprint
     *
     * Which generates
     *
     *   /assets/{target}/{md5}.min.css
     *   /assets/{target}/{md5}.min.js
     *
     * The compiled manifest is stored in APC or in the file cache.
     * So that if the touch time stamp is updated. AssetCompiler 
     * will re-compile these stuff.
     *
     * @param string target name
     * @param array Asset[]
     */
    public function compileAssets($assets, $target = '', $force = false)
    {
        $hasTarget = $target ? true : false;
        if ( $target ) {
            $cacheKey = $this->config->getNamespace() . ':' . $target;
        } else {
            $cacheKey = $this->config->getNamespace() . ':' . $this->_generateCacheKeyFromAssets($assets);
            $target = $this->config->getDefaultTarget();
        }


        if ( $cache = $this->config->getCache() ) {
            $cached = $cache->get($cacheKey);

            // cache validation
            if ( $cached && ! $force ) {
                if ( $this->productionFstatCheck ) {
                    $upToDate = true;
                    if ( $mtime = @$cached['mtime'] ) {
                        foreach( $assets as $asset ) {
                            if ( $asset->isOutOfDate($mtime) ) {
                                $upToDate = false;
                                break;
                            }
                        }
                    }
                    if ( $upToDate )
                        return $cached;
                } else {
                    return $cached;
                }
            }
        }

        $contents = array( 'js' => '', 'css' => '' );
        $assetNames = array();
        foreach( $assets as $asset ) {
            $assetNames[] = $asset->name;

            // get manifest after compiling
            $m = $this->compile($asset, $force);

            // concat results from manifest
            if (isset($m['js_file']) ) {
                $contents['js'] .= file_get_contents($m['js_file']);
            }
            if (isset($m['css_file']) ) {
                $contents['css'] .= file_get_contents($m['css_file']);
            }
        }

        // register target (assets) to the config, if it's not defaultTarget,
        if ( $hasTarget ) {
            // we should always update the target, because we might change the target assets from
            // template or php code.
            $this->config->addTarget($target, $assetNames);
            $this->config->save();
        }

        $compiledDir = $this->prepareCompiledDir();
        $compiledUrl = $this->config->getCompiledUrl();
        $outfiles = array();

        // write minified results to file
        if ($contents['js']) {
            $outfiles['js_checksum'] = hash($this->checksumAlgo, $contents['js']);
            $outfiles['js_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['js_checksum'] . '.min.js';
            $outfiles['js_url']  = "$compiledUrl/$target-" . $outfiles['js_checksum']  . '.min.js';
            file_put_contents($outfiles['js_file'], $contents['js'] );
        }

        if ($contents['css']) {
            $outfiles['css_checksum'] = hash($this->checksumAlgo, $contents['css']);
            $outfiles['css_file'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '-' . $outfiles['css_checksum'] . '.min.css';
            $outfiles['css_url'] = "$compiledUrl/$target-" . $outfiles['css_checksum'] . '.min.css';
            file_put_contents($outfiles['css_file'], $contents['css'] );
        }


        $outfiles['assets']  = $assetNames;
        $outfiles['mtime']   = time();
        $outfiles['cache_key'] = $cacheKey;
        $outfiles['target'] = $target;

        $outfiles['metafile'] = $compiledDir . DIRECTORY_SEPARATOR . $target . '.meta';
        file_put_contents($outfiles['metafile'], serialize($outfiles));

        if ( $cache = $this->config->getCache() ) {
            $cache->set($cacheKey, $outfiles);
        }
        return $outfiles;
    }

    public function setProductionFstatCheck($b)
    {
        $this->productionFstatCheck = $b;
    }

    public function enableProductionFstatCheck()
    {
        $this->productionFstatCheck = true;
    }

}




