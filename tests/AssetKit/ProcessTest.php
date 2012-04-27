<?php
use AssetKit\Process;

class ProcessTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $proc = new Process(array('ls','-1'));
        $return = $proc->run();
        is( 0, $return );

        $output = $proc->getOutput();
        ok( $output );
    }
}

