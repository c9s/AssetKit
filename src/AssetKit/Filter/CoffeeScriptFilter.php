<?php
namespace AssetKit\Filter;
use Symfony\Component\Process\ProcessBuilder;

class CoffeeScriptFilter
{
    public $coffeescript;
    public $nodejs;

    public function __construct($coffeescript, $nodejs = null )
    {
        $this->coffeescript = $coffeescript;
        if( $nodejs ) {
            $this->nodejs = $nodejs;
        }
    }

    public function filter($collection)
    {
        $input = $collection->getContent();

        if( $this->nodejs ) {
            $pb = new ProcessBuilder(array( $this->nodejs, $this->coffeescript ));
        }
        else {
            $pb = new ProcessBuilder(array( $this->coffeescript ));
        }
        // compile and print to stdout
        $pb->add( '-cp' );
        $pb->add( '--stdio');
        $pb->setInput($input);

        $proc = $pb->getProcess();
        $code = $proc->run();
        $content = $proc->getOutput();
        $collection->setContent($content);
    }
}


