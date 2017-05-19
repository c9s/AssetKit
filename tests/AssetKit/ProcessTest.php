<?php
use AssetKit\Process;

class ProcessTest extends \PHPUnit\Framework\TestCase
{
    function test()
    {
        $proc = new Process(array('ls','-1'));
        $return = $proc->run();
        $this->assertEquals( 0, $return );

        $output = $proc->getOutput();
        $this->assertNotNull( $output );
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
        $this->assertNotNull($code == 0);

        $output = $proc->getOutput();
        like('#package\.ini#',$output);
        like('#package\.xml#',$output);
    }

    function testCoffee()
    {
        $input = file_get_contents('tests/assets/test/test.coffee');
        $proc = new Process(array('coffee','-cps'));
        $code = $proc->run();
        $this->assertEquals( 0 , $code );
    }


}

