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
        like( '#README.md#',$output);
        return $output;
    }


    /**
     * @depends test
     */
    function testInput($input)
    {
        $proc = new Process(array('grep','package'));
        $code = $proc->input($input)->run();
        ok($code == 0);

        $output = $proc->getOutput();
        like('#package\.ini#',$output);
        like('#package\.xml#',$output);
    }

}

