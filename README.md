# Oops/SlimNetteBridge

[![Build Status](https://img.shields.io/travis/o2ps/SlimNetteBridge.svg)](https://travis-ci.org/o2ps/SlimNetteBridge)
[![Downloads this Month](https://img.shields.io/packagist/dm/oops/slim-nette-bridge.svg)](https://packagist.org/packages/oops/slim-nette-bridge)
[![Latest stable](https://img.shields.io/packagist/v/oops/slim-nette-bridge.svg)](https://packagist.org/packages/oops/slim-nette-bridge)

This package helps you quickly build a [Slim Framework](https://www.slimframework.com) application, utilizing the power of [Nette DI container](https://github.com/nette/di). 


## Installation and requirements

```bash
$ composer require oops/slim-nette-bridge
```

Oops/SlimNetteBridge requires PHP >= 7.1.


## Usage

Register the extension in your config file.

```yaml
extensions:
    slim: Oops\SlimNetteBridge\DI\SlimExtension(%debugMode%)
```

Then configure it:

```yaml
slim:
    settings:
        addContentLengthHeader: false
    configurators:
        - App\MyConfigurator
```

- `settings` section can be used to override Slim's [default settings](https://www.slimframework.com/docs/objects/application.html#slim-default-settings);
- `configurators` is a list of `ApplicationConfigurator` implementations which, in the same order as defined in the list, can add routes and middlewares to the instance of `Slim\App`.

Once you have configured the bridge, you can create a simple `index.php` script in your document root, using [`nette/bootstrap`](https://github.com/nette/bootstrap) to build the container:

```php
<?php

// include Composer autoloader
require_once __DIR__ . '/path/to/vendor/autoload.php';

// configure and create the DI container
$configurator = new Nette\Configurator();
$configurator->setTempDirectory(__DIR__ . '/path/to/temp');
$configurator->addConfig(__DIR__ . '/path/to/config.neon');
$container = $configurator->createContainer();

// run the configured Slim application
$container->getByType(Slim\App::class)->run();
```

Don't forget to configure your web server to pass the incoming requests to the `index.php` script.
