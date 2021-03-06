<?php
namespace AssetKit\Filter;
use RuntimeException;
use AssetKit\Process;
use AssetKit\Utils;
use AssetKit\AssetConfig;
use AssetKit\Collection;

class CoffeeScriptFilter extends BaseFilter
{
    public $coffeescript;
    public $nodejs;

    public function __construct(AssetConfig $config, $bin = null, $nodejs = null )
    {
        if ( $bin ) {
            $this->coffeescript = $bin;
        } else {
            $this->coffeescript = Utils::findbin('coffee');
        }
        if ( $nodejs ) {
            $this->nodejs = $nodejs;
        } else {
            $this->nodejs = Utils::findbin('node');
        }
        parent::__construct($config);
    }

    public function filter(Collection $collection)
    {
        if( $collection->filetype !== Collection::FileTypeCoffee ) {
            return;
        }

        $input = $collection->getContent();
        $proc = null;
        if( $this->nodejs ) {
            $proc = new Process(array( $this->nodejs, $this->coffeescript ));
        }
        else {
            $proc = new Process(array( $this->coffeescript ));
        }

        // compile and print to stdout
        $proc->arg( '-cp' )->arg('--stdio')->input($input);

        $code = $proc->run();

        if ( $code != 0 ) {
            throw new RuntimeException("CoffeeScriptFilter failure: $code");
        }

        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}


