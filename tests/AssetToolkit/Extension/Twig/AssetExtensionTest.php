<?php

class AssetExtensionTest extends Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        return array(
            new AssetToolkit\Extension\Twig\AssetExtension(),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
}

