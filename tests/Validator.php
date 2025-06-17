<?php

use Pest\Profanity\Validator;

test('empty', function () {
    expect(Validator::validateLanguages([]))->toBeEmpty();
});

test('valid', function () {
    expect(Validator::validateLanguages(['ar', 'da', 'en', 'es', 'it', 'ja', 'nl', 'pt_BR']))->toBeEmpty();
});

test('invalid', function () {
    expect(Validator::validateLanguages(['ar', 'en', 'invalid', 'es']))->toBe(['invalid']);
});
