<?php
namespace AssetToolkit\Filter;
use AssetToolkit\Process;
use AssetToolkit\Utils;
use RuntimeException;

class ScssFilter 
{
    public $scss;
    public $fromFile = true;

    public function __construct($scss = null)
    {
        if ( $scss ) {
            $this->scss = $scss;
        } else {
            $this->scss = Utils::findbin('scss');
        }
    }

    public function filter($collection)
    {
        if( ! $collection->isStylesheet )
            return;

        $proc = new Process(array( $this->scss ));
        $proc->arg('--compass');

        if($this->fromFile) {
            $filepaths = $collection->getSourcePaths(true);
            foreach($filepaths as $filepath) {
                $proc->arg($filepath);
            }
        } else {
            $content = $collection->getContent();
            $proc->arg('-s')->input( $collection->getContent() );
        }
        // compile and print to stdout
        $code = $proc->run();
        if ( $code != 0 ) {
            throw new RuntimeException("ScssFilter failure: $code");
        }
        $collection->setContent($proc->getOutput());
    }

}

