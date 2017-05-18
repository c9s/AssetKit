<?php
use AssetKit\CompilerRunner\CoffeeRunner;

class CoffeeRunnerTest extends \PHPUnit\Framework\TestCase
{
    public function testWatchCommandBuilder()
    {
        $runner = new CoffeeRunner;
        ok($runner);

        $runner->useSourceMap();
        $runner->addSourceArgument('crud.coffee');
        $runner->addSourceArgument('crud_list.coffee');
        $cmd = $runner->buildWatchCommand();
        $this->assertSame([ 'coffee', '--map', '--watch', '--compile', 'crud.coffee', 'crud_list.coffee' ], $cmd);
    }

    public function testUpdateCommandBuilder()
    {
        $runner = new CoffeeRunner;
        ok($runner);

        $runner->useSourceMap();
        $runner->addSourceArgument('crud.coffee');
        $runner->addSourceArgument('crud_list.coffee');
        $cmd = $runner->buildUpdateCommand();
        $this->assertSame([ 'coffee', '--map', '--compile', 'crud.coffee', 'crud_list.coffee' ], $cmd);
    }
}

