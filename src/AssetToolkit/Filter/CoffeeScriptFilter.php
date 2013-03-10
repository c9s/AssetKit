<?php
namespace AssetToolkit\Filter;
use RuntimeException;
use AssetToolkit\Process;
use AssetToolkit\Utils;

class CoffeeScriptFilter
{
    public $coffeescript;
    public $nodejs;

    public function __construct($bin = null, $nodejs = null )
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
    }

    public function filter($collection)
    {
        if( ! $collection->isJavascript && ! $collection->isCoffeescript ) {
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


