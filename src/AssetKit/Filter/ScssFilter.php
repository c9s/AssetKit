<?php
namespace AssetKit\Filter;
use AssetKit\Process;
use RuntimeException;

class ScssFilter 
{
    public $scss;

    public function __construct($scss = 'scss')
    {
        $this->scss = $scss;
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        $input = $collection->getContent();
        $proc = new Process(array( $this->scss ));

        // compile and print to stdout
        $proc->arg( '-s' )->arg('--scss')->input($input);
        $code = $proc->run();

        if( $code != 0 )
            throw new RuntimeException("process error: $code");
        
        $content = $proc->getOutput();
        $collection->setContent($content);
    }

}

