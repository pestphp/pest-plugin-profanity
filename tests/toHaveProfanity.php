<?php

declare(strict_types=1);

use Pest\Arch\Exceptions\ArchExpectationFailedException;

it('fails if a file contains profanity in class name', function () {
    expect('Tests\Fixtures\HasProfanityInShitClass')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);

it('fails if a file contains profanity in a comment', function () {
    expect('Tests\Fixtures\HasProfanityInComment')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);

it('fails if a file contains profanity in a method', function () {
    expect('Tests\Fixtures\HasProfanityInMethod')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);

it('fails if a file contains profanity in a property', function () {
    expect('Tests\Fixtures\HasProfanityInProperty')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);

it('fails if a file contains russian profanity', function () {
    expect('Tests\Fixtures\HasExplicitRussianProfanity')->toHaveNoProfanity(language: 'ru');
})->throws(ArchExpectationFailedException::class);

it('fails if file contains profanity manually included', function () {
    expect('Tests\Fixtures\HasUncoveredProfanity')
        ->toHaveNoProfanity(including: ['dagnabbit']);
})->throws(ArchExpectationFailedException::class);

it('fails if file contains profanity when a specific language has been set', function () {
    expect('Tests\Fixtures\HasProfanityInComment')
        ->toHaveNoProfanity(language: 'en');
})->throws(ArchExpectationFailedException::class);

it('fails if file contains profanity of multiple specified language', function () {
    expect('Tests\Fixtures\HasProfanityInComment')
        ->toHaveNoProfanity(language: ['en', 'ar']);
})->throws(ArchExpectationFailedException::class);

it('fails if file contains profanity in middle of snake_case', function () {
    expect('Tests\Fixtures\HasProfanityInAttributeAsArrayKeySnakeCase')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);

it('fails if file contains profanity in camelCase', function () {
    expect('Tests\Fixtures\HasProfanityInClassWithCamelCaseProfanityMethod')
        ->toHaveNoProfanity();
})->throws(ArchExpectationFailedException::class);
