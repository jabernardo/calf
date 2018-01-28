# Calf
![Travis: Build Status](https://travis-ci.org/jabernardo/calf.svg?branch=master "Travis: Build Status")
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Yet another Micro-framework for PHP.

## Installation

```sh

composer require jabernardo/calf

```

## Hello World

```php
<?php

require("vendor/autoload.php");

$router = new \Calf\HTTP\Router();

$home = new \Calf\HTTP\Route('/', function($req, $res) {
        return $res->set('Hello World!');
    });

$router->add($home);

$router->dispatch();

```

## Configuring Web Server

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

## Running...

```sh

php -S localhost:8888 index.php

```

## License

The `calf` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
