# URI Builder

![tests](https://github.com/JustSteveKing/uri-builder/workflows/tests/badge.svg)

A simple URI builder in PHP that is slightly opinionated

## Purpose

The purpose of this package is to provide a fluent interface to build JSON:API compliant URI strings.


## Usage

Using the built in `parse_url` in PHP will produce the following output:

```php
[
    "scheme" => "https",
    "host" => "www.domain.com",
    "path" => "/api/v1/resource"
    "query" => "include=test,another&sort=-name",
]
```

This is fine for basic usage. To use this very opinionated package:

### Building Pragmatically

```php
$url = Uri::build()
           ->addScheme('https')
           ->addHost('www.domain.com')
           ->addPath('api/v1/resource')
           ->addQuery('include=test,another&sort=-name');
```

### Creating from a String

```php
$url = Uri::fromString('https://www.domain.com/api/v1/resource?include=test,another&sort=-name')
```

### Converting back to a String

```php
$url = Uri::build()
           ->addScheme('https')
           ->addHost('www.domain.com')
           ->addPath('api/v1/resource')
           ->addQuery('include=test,another&sort=-name');

$string = $url->toString();
```
