<?php
namespace AssetKit\Exception;
use Exception;

class UnknownFragmentException extends Exception {

    public $fragment;

    public function __construct($message, $fragment) {
        $this->fragment = $fragment;
        parent::__construct($message);
    }
}
