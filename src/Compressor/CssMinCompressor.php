<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use RuntimeException;
use CssMin;

class CssMinCompressor
{
    public function compress(Collection $collection)
    {
        if (extension_loaded('cssmin')) {
            $css = cssmin($collection->getContent());
            $collection->setContent($css);
            return;
        }

        $css = CssMin::minify($collection->getContent());
        if (!$css) {
            if (CssMin::hasErrors()) {
                $errors = CssMin::getErrors();
                foreach($errors as $error) {
                    trigger_error($error->Message, E_USER_WARNING);
                }
            }
        }
        $collection->setContent($css);
    }
}

