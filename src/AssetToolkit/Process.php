<?php
namespace AssetToolkit;
use RuntimeException;

/**
 * althrough Symfony'Process is pretty good,
 * but don't like to depend on composer and pear (for both)
 *
 * so this is a simple/lightweight class for proc_open function.
 */
class Process
{
    public $args = array();

    public $input;

    public $output;

    public $error;

    public $cwd;

    public $env = array();

    public function __construct($args) 
    {
        $this->args = $args;
        $this->cwd = getcwd();
        $this->env['PATH'] = getenv('PATH'); // inherit from the PATH env

        // append default PATH env
        $this->env['PATH'] .= ':/usr/local/bin:/opt/local/bin'; // inherit from the PATH env
    }

    public function arg($arg)
    {
        $this->args[] = $arg;
        return $this;
    }

    public function input($input)
    {
        $this->input = $input;
        return $this;
    }

    public function env($name,$value)
    {
        $this->env[ $name ] = $value;
        return $this;
    }

    public function cwd($cwd)
    {
        $this->cwd = $cwd;
        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getCommand()
    {
        return join(' ', array_map(function($arg) { 
                return escapeshellarg($arg);
                    } ,$this->args));
    }

    public function run()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            // 2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
            2 => array('pipe', 'w')
        );

        $command = $this->getCommand();
        $pipes = array();
        $process = proc_open($command, $descriptorspec, $pipes, $this->cwd, $this->env);

        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // Any error output will be appended to /tmp/error-output.txt
            fwrite($pipes[0], $this->input );
            fclose($pipes[0]); // close input

            $this->output = stream_get_contents($pipes[1]);
            $this->error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]); // close stderr

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $returnValue = proc_close($process);
        }
        else {
            throw new RuntimeException;
        }
        return $returnValue;
    }

}


