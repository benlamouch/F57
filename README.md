## Easy PHTTP

Easy to use, easy to make request.

### How does this works ?

Just install this package with composer.

```bash
php composer require f57/easyphttp
```

And that's all !
Just try it :

```php
use F57\EasyPHTTP;

EasyPHTTP::url('https://httpbin.org/get')
            ->getResponse();
```

### Methods

#### url
