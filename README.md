# fauxton

[![Build Status](https://travis-ci.org/php-api-clients/fauxton.svg?branch=master)](https://travis-ci.org/php-api-clients/fauxton)
[![codecov](https://codecov.io/gh/php-api-clients/fauxton/branch/master/graph/badge.svg)](https://codecov.io/gh/php-api-clients/fauxton)

Async version of the [fauxton-client](https://github.com/ace411/fauxton-client) powered by [ReactPHP](https://reactphp.org). As such, it offers the cogencies of CouchDB interactions in a non-blocking IO environment.

## Requirements

- PHP 7.1+

## Installation

The fauxton library is available on Packagist. To install it, type the following on your preferred
command-line interface:

```
composer require php-api-clients/fauxton
```

## Documentation

I advise that you read the [official CouchDB documentation](http://docs.couchdb.org) 
so as to better understand fauxton. Also, check out the examples in the [examples folder](https://github.com/php-api-clients/fauxton-client/tree/master/examples) for more perspective.

## Running the unit tests

Follow the following steps:

- Create a database called ```testdb```

- Type the following in a command line of your choosing:

```
vendor/bin/phpunit -c phpunit.xml.dist
```

## Dealing with problems

Endeavor to create an issue on GitHub when the need arises or send an email to lochbm@gmail.com
