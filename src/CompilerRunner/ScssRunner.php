<?php
namespace AssetKit\CompilerRunner;
use InvalidArgumentException;

class ScssRunner implements CompilerRunnerInterface
{

    const VERSION = "1.3.1";

    public $bin = 'scss';

    public $enableCompass = false;

    public $targets = array();

    public $force = false;

    public $style;

    public $quiet = false;

    public $debug = false;

    public $loadPaths = array();

    public $sourceMap;

    public function __construct($bin = null) {
        if ($bin) {
            $this->bin = $bin;
        }
    }

    public function addLoadPath() {
        $paths = func_get_args();
        $this->loadPaths = array_merge($this->loadPaths, $paths);
        return $this;
    }

    public function setQuiet($quiet = true) 
    {
        $this->quiet = $quiet;
        return $this;
    }

    public function setForce($force = true)
    {
        $this->force = $force;
        return $this;
    }


    public function setDebug($debug = true)
    {
        $this->debug = $debug;
        return $this;
    }

    public function enableCompass($enable = true)
    {
        $this->enableCompass = $enable;
        return $this;
    }

    public function addTarget($from, $to = null) 
    {
        $this->targets[] = array($from, $to);
        return $this;
    }

    public function setStyle($style) {
        $this->style = $style;
        return $this;
    }

    public function setSourceMap($sourceMap) {
        $this->sourceMap = $sourceMap;
        return $this;
    }


    public function buildBaseCommand($force = false) {
        $cmd = array($this->bin);
        if ( $this->enableCompass ) {
            $cmd[] = '--compass';
        }
        if ($this->sourceMap) {
            $cmd[] = '--sourcemap';
            $cmd[] = $this->sourceMap;
        }
        if ($this->force || $force) {
            $cmd[] = '--force';
        }
        if ( $this->style ) {
            $cmd[] = '--style';
            $cmd[] = $this->style;
        }
        if ( $this->quiet ) {
            $cmd[] = '--quiet';
        }
        if ( $this->debug ) {
            $cmd[] = '-g';
        }

        $cmd = array_merge($cmd, $this->buildLoadPathList());
        return $cmd;
    }

    public function buildLoadPathList() {
        $list = array();
        foreach( $this->loadPaths as $path ) {
            $list[] = '--load-path';
            $list[] = $path;
        }
        return $list;
    }

    public function buildTargetList() {
        $list = array();
        foreach( $this->targets as $target ) {
            if (is_string($target)) {
                $list[] = $target;
            } elseif (is_array($target)) {
                list($from, $to) = $target;
                if ($to) {
                    $list[] = "$from:$to";
                } else {
                    $list[] = $from;
                }
            } else {
                throw new InvalidArgumentException("Invalid argument type for building target list.");
            }

        }
        return $list;
    }

    public function addSourceArgument($argument)
    {
        $this->targets[] = $argument;
    }

    public function buildWatchCommand() {
        $cmd = $this->buildBaseCommand();
        $cmd[] = '--watch';
        return array_merge($cmd, $this->buildTargetList());
    }

    public function buildUpdateCommand() {
        $cmd = $this->buildBaseCommand();
        $cmd[] = '--update';
        return array_merge($cmd, $this->buildTargetList());
    }

    public function update()
    {
        if ($force) {
            $this->force = $force;
        }
        $cmd = $this->buildUpdateCommand();
        system(join(" ", $cmd));
    }

    public function check()
    {
        $cmd = $this->buildBaseCommand($force);
        $cmd[] = '--check';
        $cmd = array_merge($cmd, $this->buildTargetList());

        // TODO: use symfony process builder 
        system( join(" ", $cmd) );
    }

    public function watch($force = false)
    {
        if ($force) {
            $this->force = $force;
        }
        $cmd = $this->buildWatchCommand();
        system( join(" ", $cmd) );
    }

}

