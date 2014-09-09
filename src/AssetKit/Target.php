<?php
namespace AssetKit;

class Target
{
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getMetaFilename() {
        return $this->id . '.meta.php';
    }
}



