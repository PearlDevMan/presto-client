# Presto 

[![](https://img.shields.io/packagist/php-v/clouding/presto.svg?style=flat-square)](https://packagist.org/packages/clouding/presto)
[![](https://img.shields.io/packagist/v/clouding/presto.svg?style=flat-square)](https://packagist.org/packages/clouding/presto)
[![](https://img.shields.io/travis/com/cloudingcity/presto.svg?style=flat-square)](https://travis-ci.com/cloudingcity/presto)
[![](https://img.shields.io/codecov/c/github/cloudingcity/presto.svg?style=flat-square)](https://codecov.io/gh/cloudingcity/presto)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)

A [Presto](https://prestodb.io) http client for the [PHP](http://www.php.net/) programming language.

> Inspired from [illuminate/database](https://github.com/illuminate/database)

## Installation

```
composer require clouding/presto
```

## Quick Start

Create a presto manager
```php
use Clouding\Presto\Presto;

$presto = new Presto();

$presto->addConnection([
    'host' => 'localhost:8080',
    'catalog' => 'default',
    'schema' => 'presto',
]);

// Set manager as global (optional)
$presto->setAsGlobal();
```

Get a default connection and send query
```php
$posts = $presto->connection()->query('select * from posts')->get();
```

If set manager as global, just query directly and get data with [collection](https://github.com/tightenco/collect)
```php
$posts = Presto::query('select * from posts')->get();
var_dump($posts->toArray()); // [[1, 'Good pracetice'], [2, 'Make code cleaner']]

$posts = Presto::query('select * from posts')->getAssoc();
var_dump($posts->toArray()); // [['id' => 1, 'title' => 'Good pracetice'], ['id' => 2, 'title' => 'Make code cleaner']]
```

## Usage

### Multiple connections

```php
use Clouding\Presto\Presto;

$presto = new Presto();

$presto->addConnection([
    'host' => 'localhost:8080',
    'catalog' => 'default',
    'schema' => 'presto',
]);

$presto->addConnection([
    'host' => 'localhost:8080',
    'catalog' => 'default2',
    'schema' => 'presto2',
], 'presto2');

$presto->setAsGlobal();

// Get connections
$connections = Presto::getConnections();

// Specify connection
$posts = Presto::query('select * from posts', 'presto2')->get();
```

## Running Tests
```
composer test
```
