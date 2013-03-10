<?php
namespace AssetToolkit\Filter;
use AssetToolkit\Collection;
use AssetToolkit\Process;
use RuntimeException;

class SassFilter 
{
    public $sass;
    public $fromFile = true;

    public function __construct($sass = 'sass')
    {
        $this->sass = $sass;
    }

    public function filter(Collection $collection)
    {
        if( $collection->filetype !== Collection::FILETYPE_SASS )
            return;

        $proc = new Process(array( $this->sass ));
        $proc->arg('--compass');

        if($this->fromFile) {
            $filepaths = $collection->getSourcePaths(true);
            foreach( $filepaths as $filepath ) {
                $proc->arg($filepath);
            }
        } else {
            $proc->arg('-s');
            $proc->input($collection->getContent());
        }


        $code = $proc->run();
        if ( $code != 0 ) {
            throw new RuntimeException("SassFilter failure: $code. ");
        }
        $collection->setContent($proc->getOutput());
    }

}

