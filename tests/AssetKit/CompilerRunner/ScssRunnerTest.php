<?php
use AssetKit\CompilerRunner\ScssRunner;

class ScssRunnerTest extends \PHPUnit\Framework\TestCase
{
    public function testWatchCommandBuilder()
    {
        $runner = new ScssRunner;
        $runner->enableCompass();
        $runner->setForce();
        $runner->addSourceArgument('sass:css');
        $runner->addSourceArgument('foo.sass:foo.css');
        $cmd = $runner->buildWatchCommand();
        $this->assertSame([
            'scss',
            '--compass',
            '--force',
            '--watch',
            'sass:css', 'foo.sass:foo.css'
        ], $cmd);
    }

    public function testUpdateCommandBuilder()
    {
        $runner = new ScssRunner;
        $runner->enableCompass();
        $runner->setForce();
        $runner->addSourceArgument('sass:css');
        $runner->addSourceArgument('foo.sass:foo.css');
        $cmd = $runner->buildUpdateCommand();
        $this->assertSame([
            'scss',
            '--compass',
            '--force',
            '--update',
            'sass:css', 'foo.sass:foo.css'
        ], $cmd);
    }
}

