# Pattern Recognition
A pattern matcher for PHP arrays.

Need to pick out an array based on a subset of its key value pairs? Say you've got:

```php
['x' => 1]            -> A
['x' => 1, 'y' => 1]  -> B
['x' => 1, 'y' => 2 ] -> C
```

Then this library would give you:

```php
['x' => 1]           -> A
['x' => 2]           -> no match
['x' => 1, 'y' => 1] -> B
['x' => 1, 'y' => 2] -> C
['x' => 2, 'y' => 2] -> no match
['y' => 1]           -> no match
```
## Quick Example

```php
$pm = new Yuloh\PatternRecognition\Matcher;

$pm
    ->add(['a' => 1], 'A')
    ->add(['b' => 2], 'B');

$pm->find(['a' => 1]); // returns 'A'
$pm->find(['a' => 2]); // returns null
$pm->find(['a' => 1, 'b' => 1]); // returns 'A'. 'b' => 1 is ignored, it was never registered.
```

Since you are matching a subset, the pattern to find can contain any number of extra key value pairs.  

## Install

```bash
composer require yuloh/pattern-recognition
```
## Why

Since this library is a port of the javascript library patrun, [the rationale](https://github.com/rjrodger/patrun#the-why) is the same.  This library lets you build a simple decision tree to avoid writing if statements.

## Rules

1. More specific matches beat less specific matches. That is, more key value pairs beat fewer.
2. Array keys are checked in alphabetical order.
3. Exact matches are more specific than globs.
4. Matches are more specific than root matches.

```php
$pm = (new Matcher())
    ->add(['a' => 0], 'A')
    ->add(['c' => 2], 'C')
    ->add(['a' => 0, 'b' => 1], 'AB')
    ->(['a' => '*'], 'AG')
    ->add([], 'R');

$pm->find(['a' => 0, 'b' => 1]); // 'AB', because more specific matches beat less specific matches.
$pm->find(['a' => 0, 'c' => 2]); // 'A', because a comes before c and keys are checked in alphabetical order. 
$pm->find(['a' => 0]); // 'A' as exact match, because exact matches are more specific than globs.
$pm->find(['a' => 2]); // 'AG' as glob match, as matches are more specific than root matches.
$pm->find(['b' => 2]); // 'R', as root match.
```

## API

```
add(array $pattern, mixed $data)
```

Register a pattern, and the data that will be returned if an input matches.  Both keys and values are considered to be strings. Other types are converted to strings.

```
remove(array $pattern)
```

Remove this pattern, and it's data, from the matcher.

```
find(array $pattern)
```

Find the given pattern and return it's data.

```
jsonSerialize()
```

Serializes the object to a value that can be serialized natively by json_encode().

```
toArray()
```

Serializes the matcher to an array of the registered matches and data.
