<?php
namespace AssetKit\Compressor;
use AssetKit\Collection;
use AssetKit\Process;
use AssetKit\JSMin;
use RuntimeException;
use Exception;

class JsMinExtException extends Exception {  }

class JsMinExtCompressor
{
    public $messages = array(
        JSMIN_ERROR_UNTERMINATED_STRING => 'Unterminated string.',
        JSMIN_ERROR_UNTERMINATED_COMMENT => 'Unterminated comment.',
        JSMIN_ERROR_UNTERMINATED_REGEX => 'Unterminated regex.',
    );

    public static function support() {
        return extension_loaded('jsmin');
    }

    public function compress(Collection $collection)
    {
        // C version jsmin is faster,
        $content = $collection->getContent();
        $content = jsmin($content);
        $err = jsmin_last_error_msg();
        if ($err != JSMIN_ERROR_NONE) {
            if (isset($this->messages[$err])) {
                throw new Exception($this->messages[$err]);
            } else {
                throw new Exception("Unknown Error");
            }
        }
        $collection->setContent($content);
    }
}



