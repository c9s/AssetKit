<?php
namespace AssetKit\CompilerRunner;
use InvalidArgumentException;

class CoffeeRunner implements CompilerRunnerInterface
{
    public $bin = 'coffee';

    public $targets = array();

    public $bare = false;

    public $sourceMap;

    public $join;

    public function __construct($bin = null) {
        if ($bin) {
            $this->bin = $bin;
        }
    }

    public function useJoin() {
        $this->join = true;
        return $this;
    }

    public function useSourceMap() {
        $this->sourceMap = true;
        return $this;
    }

    public function buildBaseCommand($force = false) {
        $cmd = array($this->bin);
        if ($this->bare) {
            $cmd[] = '--bare';
        }
        if ($this->sourceMap) {
            $cmd[] = '--map';
        }
        if ($this->join) {
            $cmd[] = '--join';
        }
        return $cmd;
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
        $cmd[] = '--compile';
        return array_merge($cmd, $this->buildTargetList());
    }

    public function buildUpdateCommand() {
        $cmd = $this->buildBaseCommand();
        $cmd[] = '--compile';
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

