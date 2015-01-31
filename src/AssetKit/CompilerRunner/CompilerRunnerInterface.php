<?php
namespace AssetKit\CompilerRunner;

interface CompilerRunnerInterface
{
    /**
     * Add source argument to the command. e.g.
     *
     *    foo.coffee
     *
     * And it will be in the command line:
     *
     *    coffee -w foo.coffee
     *
     * Or: "sass:css", "sass/foo.sass"... and it will be:
     *
     *    sass -wc sass:css sass/foo.sass
     *
     * @param string $argument
     */
    public function addSourceArgument($argument);

    /**
     * Build the command of watching files...
     */
    public function buildWatchCommand();

    /**
     * Build the command to build files but not watch them
     *
     * @return string
     */
    public function buildUpdateCommand();
}


