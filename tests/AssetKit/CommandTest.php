<?php
use CLIFramework\Testing\CommandTestCase;

class CommandTest extends CommandTestCase
{

    public function setupApplication()
    {
        return AssetKit\Console::getInstance();
    }

    public function setUp() 
    {
        parent::setUp();
        ob_start();
        $this->runCommand('assetkit init --baseDir public/assets --baseUrl /assets --dir tests/assets assetkit.yml');
        ob_end_clean();
    }


    public function testAddAndRemove()
    {
        ob_start();
        $this->runCommand('assetkit add tests/assets/jquery');
        $this->runCommand('assetkit list');
        $this->runCommand('assetkit remove jquery');
        ob_end_clean();
    }

    public function testCompile()
    {
        ob_start();
        $this->runCommand('assetkit add tests/assets/jquery');
        $this->runCommand('assetkit add tests/assets/underscore');
        $this->runCommand('assetkit add tests/assets/webtoolkit');
        $this->runCommand('assetkit add tests/assets/jquery-ui');
        $this->runCommand('assetkit compile jquery');
        $this->runCommand('assetkit compile --target all jquery underscore webtoolkit jquery-ui');
        ob_end_clean();
    }
}



