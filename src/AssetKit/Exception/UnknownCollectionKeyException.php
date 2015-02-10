<?php
namespace AssetKit\Exception;
use InvalidArgumentException;

class UnknownCollectionKeyException extends InvalidArgumentException
{
    protected $stash;

    public function __construct($message, array $stash = array()) {
        parent::__construct($message);
        $this->stash = $stash;
    }

    public function getStash()
    {
        return $this->stash;
    }

    public function __toString()
    {
        return parent::__toString() . ':' . var_export($this->stash, true);
    }
}




