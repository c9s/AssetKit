Asset
============

Asset config contains registered assets, and asset directories.

```php
$config = new AssetToolkit\AssetConfig( '../.assetkit');
$loader = new AssetToolkit\AssetLoader( $config );
$assets[] = $loader->load( 'jquery' );
$assets[] = $loader->load( 'jquery-ui' );
$assets[] = $loader->load( 'extjs4-gpl' );
```

The asset config spec:


```js
{
    baseDir: 'public'
    baseUrl: '/assets'
}
```
