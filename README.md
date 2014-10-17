# PHP Code Formatter

[![Build Status](https://travis-ci.org/gossi/php-code-formatter.svg?branch=master)](https://travis-ci.org/gossi/php-code-formatter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gossi/php-code-formatter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gossi/php-code-formatter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/gossi/php-code-formatter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gossi/php-code-formatter/?branch=master)

A code formatting library for php code.


## Features

- Whitespace
- New lines
- Indentation (on curly braces)

## Wishlist

- Blank lines
- Smart Indentation for conditions
- Indentation for chaining methods (some symfony libs and propel generated methods)
- Array Indentation
- Run through CLI


## Developer Docs

### Definitions

#### Entity: Block

A block is everything between curly braces.

##### Struct

Abstract word that defines `class`, `trait` and `interface`.

##### Routine

Which is either a function or method.

##### Block

Everything between curly braces which is neither a Struct or Routine, something like loops, conditions, etc. (but also use blocks).

#### Entity: Group

A group is everything between parens.

##### Block

When a group belongs to a block, such as the conditions for an if-clause or a while statement.

##### Call

Whenever a function or method is invocated, this will be the respective context

Example:

```
$foo->bar($baz);
```

##### Group

Everything else, that is grouped within parens. 

Example: 

```
$a = $b * ($c + 5);
```

#### Entity: Unit

Units are collective statements of a specific type. 

Example:

```
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
```

### ContextManager

The context manager helps you traversing the tokens and keeping track of entering or leaving a block, group and line contexts. Helpful methods:

```
$context->getCurrentContext(); // file, struct, routine, block, group
```




