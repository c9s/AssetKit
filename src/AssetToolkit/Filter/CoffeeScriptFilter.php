<?php
namespace AssetToolkit\Filter;
use RuntimeException;
use AssetToolkit\Process;

class CoffeeScriptFilter
{
    public $coffeescript;
    public $nodejs;

    public function __construct($coffeescript = 'coffee', $nodejs = null )
    {
        $this->coffeescript = $coffeescript;
        if( $nodejs ) {
            $this->nodejs = $nodejs;
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
            throw new RuntimeException("Process error: $code");
        }

        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}


