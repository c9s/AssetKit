<?php
namespace AssetKit\Filter;
use AssetKit\Process;
use RuntimeException;

class ScssFilter 
{
    public $sass;

    public function __construct($sass = 'sass')
    {
        $this->sass = $sass;
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        $input = $collection->getContent();
        $proc = new Process(array( $this->sass ));

        // compile and print to stdout
        $proc->arg( '-s' )->arg('--scss')->input($input);
        $code = $proc->run();

        if( $code != 0 )
            throw new RuntimeException("process error: $code");
        
        $content = $proc->getOutput();
        $collection->setContent($content);
    }

}

