<?php
namespace AssetKit\Filter;
use AssetKit\Process;
use RuntimeException;

class SassFilter 
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
        $proc = new Process(array( $this->sass ));
        $filepaths = $collection->getSourcePaths(true);
        foreach( $filepaths as $filepath ) {
            $proc->arg($filepath);
        }
        $code = $proc->run();
        if( $code != 0 )
            throw new RuntimeException("process error: $code. ");
        $collection->setContent($proc->getOutput());
    }

}

