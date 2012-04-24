<?php
namespace AssetKit\Filter;

class CoffeeScriptFilter
{
    public $coffeescript;
    public $nodejs;

    public function __construct($coffeescript, $nodejs = 'node' )
    {
        $this->nodejs = $nodejs;
        $this->coffeescript = $coffeescript;
    }

    public function filter($collection)
    {
        $pb = new ProcessBuilder(array( $this->nodejs, $this->coffeescript ));
        // compile and print to stdout
        $pb->add( '-cp' );

        $input = $collection->getContent();
        $proc = $pb->getProcess();
        $proc->setInput($input);
        $code = $proc->run();
        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}


