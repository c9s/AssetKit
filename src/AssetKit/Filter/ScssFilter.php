<?php
namespace AssetKit\Filter;
use AssetKit\Process;
use RuntimeException;

class ScssFilter 
{
    public $scss;

    public function __construct($sass = 'scss')
    {
        $this->scss = $scss;
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        $proc = new Process(array( $this->scss ));
        $filepaths = $collection->getSourcePaths(true);
        foreach($filepaths as $filepath) {
            $proc->arg($filepath);
        }
        // compile and print to stdout
        $code = $proc->run();
        if( $code != 0 )
            throw new RuntimeException("process error: $code");
        $collection->setContent($proc->getOutput());
    }

}

