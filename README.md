## Easy PHTTP
Easy to use, easy to make request.

### How does this works ?
Just install guzzle and this package with composer.

```bash
php composer require guzzlehttp/guzzle:~6.0
php composer require F57/EasyPHTTP
```

And that's all !
Just try it : 

```php
EasyPHTTP::url('https://httpbin.org/get')
            ->getResponse();
```

### Methods 

#### url