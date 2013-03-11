<?php
namespace AssetToolkit\Filter;
use AssetToolkit\Process;
use AssetToolkit\Utils;
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
