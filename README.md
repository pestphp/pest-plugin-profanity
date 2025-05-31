<img src="art/banner.png">

# Profanify
A PestPHP Plugin that helps catch profanity in your application.

[![Static Analysis](https://github.com/JonPurvis/profanify/actions/workflows/static.yml/badge.svg)](https://github.com/JonPurvis/lawman/actions/workflows/static.yml)
[![Tests](https://github.com/JonPurvis/profanify/actions/workflows/tests.yml/badge.svg)](https://github.com/JonPurvis/lawman/actions/workflows/tests.yml)
![GitHub last commit](https://img.shields.io/github/last-commit/jonpurvis/profanify)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/jonpurvis/profanify/php)
![GitHub issues](https://img.shields.io/github/issues/jonpurvis/profanify)
![GitHub](https://img.shields.io/github/license/jonpurvis/profanify)
![Packagist Downloads](https://img.shields.io/packagist/dt/jonpurvis/profanify)

## Introduction
Profanify is a PestPHP Plugin designed to detect and flag instances of profanity within your application. As developers, 
we've all faced moments of frustration, whether it be debugging a persistent issue or trying to decipher confusing code 
written by someone else. These moments can sometimes result in the inclusion of profanity in your code.

Whilst this might be fine during local development, it's important to remove any profanity before pushing 
your changes, so your past frustration doesn't end up being in production and in the local version of whoever else 
works on your application. It also means your codebase is kept professional and respectful. 

This is where Profanify comes in, the package makes it really easy to test your application for common swear words, that
you may have forgotten about or had no idea they were in there in the first place. If you have your tests running as
part of a CI/CD pipeline, then it should mean the pipeline will fail if there's any profanity in your codebase.

## Installation

To install Profanify, you can run the following command in your project's root:

```text
composer require jonpurvis/profanify --dev
```

## Examples

Let's take a look at how Profanify works. There's not much to it but because it's a PestPHP plugin, we can use the
functionality that Pest provides out of the box, and combine it with what Profanify offers.

Let's take the following scenario, let's say we have this in our application:

```php
/**
 * Adds 2 numbers together
 * 
 * @TODO make this less fucking stupid, why doesn't it just add them up?!? Absolute shit 
 */
protected function addNumbers($a, $b): int
{
    $str = $a . ',' . $b;
    $arr = explode(',', $str);
    
    $num1 = (int)$arr[0];
    $num2 = (int)$arr[1];
    
    $sum = ($num1 + 0) + ($num2 * 1);
    
    return $sum;
}
```

So yes, the method is overly complex for what it does, we can see someone has added a TODO comment, in an effort
to refactor it and make it nicer. Unfortunately, they've resorted to using profanity so if we were to write and run the
following test:

```php
expect('App')
    ->toHaveNoProfanity()
```

The test suite would fail, because there's multiple usages of Profanity in one of the files. You don't have to only
test the whole application though, you could limit it to, for example, Controllers:

```php
expect('App\Http\Controllers')
    ->toHaveNoProfanity()
```

You could even expect profanity, if you really wanted to:
```php
expect('App\Providers')
    ->not->toHaveNoProfanity()
```

There may also be times when you want to ignore certain phrases included in the profanity list. To do this, you can pass an `excluding` argument to the `toHaveNoProfanity` method. This argument should be an array of strings that you want to ignore. For example:

```php
expect('App')
    ->toHaveNoProfanity(excluding: ['69']);
```

In the test above, the test would pass even if the word `69` was included in one of the tested files.

Or, you may want to test for profanity not included in the list. To do this, pass an `including` argument to the `toHaveNoProfanity` method. This argument should be an array of strings you also want to consider as profanity. For example:

```php
expect('App')
    ->toHaveNoProfanity(including: ['dagnabbit']);
```

If a test does fail because of Profanity, then the output will show the offending file and line. IDE's such as PHPStorm,
will allow you to click the file and be taken straight to the line that contains profanity:

```bash
Expecting 'tests/Fixtures/HasProfanityInComment.php' to not use profanity.
at tests/Fixtures/HasProfanityInComment.php:10

  Tests:    1 failed (1 assertions)
  Duration: 0.06s
```

By default, Profanify will scan all language files, which may cause some problems if a word in your language is fine but
is listed as profane in another language. To combat this, you can specify a default language, which means only that file will be 
checked against when the test runs:

```php
expect('App')
    ->toHaveNoProfanity(language: 'en');
```

The example above means that only profanity in `Config/profanities/en.php` file will be picked up.

```php
expect('App')
    ->toHaveNoProfanity(language: ['en', 'ar']);
```

This will check for profanities in both languages `en` and `ar`.

## Languages
Profanify currently supports the following languages:

- Arabic
- Danish
- Dutch
- English
- Japanese
- Italian
- Portuguese
- Spanish

Each language has its own configuration file. If you'd like to add a language, please create a new configuration file.

## Contributing
Contributions to the package are more than welcome as there's bound to be extra words that need adding. Feel free to 
submit a Pull Request with any additions. If you have any issues using the package, then please open an Issue. 

## Useful Links & Credit
- [PestPHP](https://pestphp.com/)
- Thanks to [`surge-ai/profanity`](https://github.com/surge-ai/profanity) for the original list of words 
