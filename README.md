# container-interop/service-provider Drupal 8 module

Import `service-provider` as defined in `container-interop` into a Drupal 8 project.

## Usage

### Installation

Install the package using Composer:

```js
composer require thecodingmachine/drupal-service-provider-bridge
```

Go to Drupal administration panel, to the "Extensions" page and enable the "Service providers integration" module.

### Puli fix

There is currently an issue with Puli loading.
To fix it, create a `load_puli.php` file at the root of your project:

**load_puli.php**
```php
<?php
if (file_exists(__DIR__.'/.puli/GeneratedPuliFactory.php')) {
    require_once __DIR__.'/.puli/GeneratedPuliFactory.php';
}
```

Now, go to your project `composer.json` file and in the `autoload` section, add:

```js
{
    "autoload": {
        "files": [
            "load_puli.php"
        ]
    },
}
```

### Usage using Puli

The bridge bundle will use Puli to automatically discover the service providers of your project. If the service provider you are loading publishes itself
on Puli, then you are done. The services declared in the service provider are available in the Symfony container!

### Usage using manual declaration
 
If the service provider you are using does not publishes itself using Puli, you will have to declare it manually.
To do so, open a `service-providers.php` file at the web-root of your project, and return the list of service provider you want to import:

**service-providers.php**
```php
return [
    'service-providers' => [
        'My\\Project\\Di\\MyServiceProvider',
        'My\\Project\\Di\\MyOtherServiceProvider',
    ]
];
```


## Disabling Puli discovery

You can disable Puli discovery by passing `'puli' => false` in the `service-providers.php` file:

**service-providers.php**
```php
return [
    'service-providers' => [
        'My\\Project\\Di\\MyServiceProvider',
        'My\\Project\\Di\\MyOtherServiceProvider',
    ],
    'puli' => false
];
```

Note: instead of returning a fully-qualified class name, you can also put in the array an instance of a service provider directly.

## Known limitations

Drupal 8 container only accepts **lower-case identifiers**.

Since service providers can provide any kind of identifiers for services (both upper and lower case), this bridge systematically put cast the identifiers in lower-case.

This can introduce bugs if you have 2 services that have the same name in different cases (but honnestly, you should reconsider the way you design your service providers if you have this issue :) )


## Default aliases

By default, this package provides will create the following entries:

- `Psr\Log\LoggerInterface` => alias to `logger.channel.default` 
- `puli_factory` => The Puli factory
- `puli_repository` => The Puli repository
- `puli_discovery` => The Puli discovery service

## How it works

Behind the scene, this Drupal 8 module heavily relies on the [Symfony <=> service-provider bridge bundle](https://github.com/thecodingmachine/service-provider-bridge-bundle).
Indeed, Drupal 8 container is a heavily adapted container based on Symfony container. This module is therefore a set of adaptations from the Symfony bridge.
