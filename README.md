# Laravel Ezyslips

Laravel Ezyslips is a simple package which helps to build robust integration into Ezyslips.

## Installation

Add package to composer.json

    composer require clarity-tech/laravel-ezyslips

### Laravel 5.5+

Package auto discovery will take care of setting up the alias and facade for you


### Laravel 5.4 <

Add the service provider to config/app.php in the providers array.

```php5
<?php

'providers' => [
    ...
    ClarityTech\Ezyslips\EzyslipsServiceProvider::class,
],
```


Setup alias for the Facade

```php5
<?php

'aliases' => [
    ...
    'Ezyslips' => ClarityTech\Ezyslips\Facades\Ezyslips::class,
],
```

## Set credendials

in your `.env` file set these values from your app
`EZYSLIPS_EMAIL=your-email`
`EZYSLIPS_LICENSE_KEY=your-license-key`

## Optional Configuration (Publishing)

Laravel Ezyslips requires you to set email and license key configuration. You will need to publish configs assets

    `php artisan vendor:publish --tag=ezyslips-config`

This will create a ezyslips.php file in the config directory.
```
'key' => env("EZYSLIPS_EMAIL", null),
'secret' => env("EZYSLIPS_LICENSE_KEY", null)
```


## Usage

Just set the env and use the Ezyslips facade or resolve it in the container

```php
use ClarityTech\Ezyslips\Facades\Ezyslips;

return Ezyslips::api()->order->create(
    $attributes
);
```


## Controller Example

If you prefer to use dependency injection over facades like me, then you can inject the Class:

```php5
use Illuminate\Http\Request;
use ClarityTech\Ezyslips\Ezyslips;

class Foo
{
    protected $ezyslips;

    public function __construct(Ezyslips $ezyslips)
    {
        $this->ezyslips = $ezyslips;
    }
    /*
    * returns a single order
    */
    public function getOrder(Request $request)
    {
        $orders = $this->ezyslips
            ->order->fetch(['orderid' => 40586]);
    }

    /*
    * returns the array of orders
    */
    public function getOrder(Request $request)
    {
        $orders = $this->ezyslips
            ->order->all(['status' => 'A']);
    }

    /*
    * Advanced use case call the api directly
    * with specifying the prefix
    * returns the raw responses
    */
    public function getOrder(Request $request)
    {
        $orders = $this->ezyslips
            ->get('getorders');

    }
}
```

## Miscellaneous

To get Response headers

```php5
Ezyslips::getHeaders();
```

To get specific header
```php5
Ezyslips::getHeader("Content-Type");
```

Check if header exist
```php5
if(Ezyslips::hasHeader("Content-Type")){
    echo "Yes header exist";
}
```

To get response status code or status message
```php5
Ezyslips::getStatusCode(); // 200
Ezyslips::getReasonPhrase(); // ok
```














