# PHP Code Formatter

[![Build Status](https://travis-ci.org/gossi/php-code-formatter.svg?branch=master)](https://travis-ci.org/gossi/php-code-formatter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gossi/php-code-formatter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gossi/php-code-formatter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/gossi/php-code-formatter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gossi/php-code-formatter/?branch=master)

A library for formatting php code.


## Features

- Whitespace
- New lines
- Indentation (on curly braces only)
- Blanks (partial support)

-> [Wishlist](https://github.com/gossi/php-code-formatter/labels/feature-request)

## Getting started

### Installation

Via composer:

```json
{
    "require": {
        "gossi/php-code-formatter": "dev-master"
    }
}
```

### From Code

This simple code snippet is all you need:

```php
use gossi\formatter\Formatter;

$formatter = new Formatter();
$beautifulCode = $formatter->format($uglyCode);
```

### From CLI

Not yet, see [#2](https://github.com/gossi/php-code-formatter/issues/2)

## Development

php code formatter is not yet finished (see [Wishlist](https://github.com/gossi/php-code-formatter/labels/feature-request)). Please help the development, by picking one of the open issues or implement your own rules. See the wiki on [creating your own rules](https://github.com/gossi/php-code-formatter/wiki/creating-your-own-Rules).

Psr-2? Spaces suck, deal with it :p Once [Version 1.0](https://github.com/gossi/php-code-formatter/milestones/Version%201.0) is reached, a psr-2 profile will be shipped.

