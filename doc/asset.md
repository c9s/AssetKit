Asset
============

Asset config contains registered assets, and asset directories.

```php
$config = new AssetKit\Config( '../.assetkit');
$loader = new AssetKit\AssetLoader( $config );
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
