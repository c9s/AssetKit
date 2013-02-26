File Collection
===============

The API
-------

To create file collections from asset object:

```php
$collections = FileCollection::create_from_manifest($asset);
```

To get file collections from existing asset object (which actually uses `FileCollection::create_from_manifest`:

```php
// install into public asset root.
foreach( $asset->getFileCollections() as $collection ) {
    foreach( $collection->getFilePaths() as $path ) {
        $subpath = $path;
        $srcFile = $fromDir . DIRECTORY_SEPARATOR . $subpath;

        if( ! file_exists($srcFile) ) {
            $this->log("$srcFile not found.");
            continue;
        }

        $targetFile = $asset->config->getPublicAssetRoot() . DIRECTORY_SEPARATOR . $n . DIRECTORY_SEPARATOR . $subpath;
        if( file_exists($targetFile) ) {
            unlink($targetFile);
        }
        FileUtils::mkdir_for_file( $targetFile );
        $this->log("* $targetFile");
        symlink(realpath($srcFile),$targetFile) 
                or die("$targetFile link failed.");
    }
}
```

