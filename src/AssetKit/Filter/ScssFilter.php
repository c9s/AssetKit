<?php
namespace AssetKit\Filter;
use AssetKit\Process;
use AssetKit\Utils;
use RuntimeException;

class ScssFilter extends SassFilter
{
    public function __construct($bin = null)
    {
        if ( $bin ) {
            $this->bin = $bin;
        } else {
            $this->bin = Utils::findbin('scss');
        }
    }
}
