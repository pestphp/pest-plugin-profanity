<?php

declare(strict_types=1);

use JonPurvis\Profanify\Expectations\TargetedProfanity;
use JonPurvis\Profanify\Support\Russian;
use Pest\Arch\Contracts\ArchExpectation;
use Pest\Arch\Support\FileLineFinder;
use PHPUnit\Architecture\Elements\ObjectDescription;

expect()->extend('toHaveNoProfanity', fn (array $excluding = [], array $including = [], $language = null): ArchExpectation => TargetedProfanity::make(
    $this,
    function (ObjectDescription $object) use (&$foundWords, $excluding, $including, $language): bool {
        $words = [];
        $profanitiesDir = __DIR__.'/../Config/profanities';

        if (($profanitiesFiles = scandir($profanitiesDir)) === false) {
            return true;
        }

        $profanitiesFiles = array_diff($profanitiesFiles, ['.', '..']);

        if ($language) {
            $languages = is_string($language) ? [$language] : $language;

            foreach ($languages as $lang) {
                $specificLanguage = "$profanitiesDir/$lang.php";
                if (file_exists($specificLanguage)) {
                    $words = array_merge(
                        $words,
                        include $specificLanguage
                    );
                }
            }
        } else {
            foreach ($profanitiesFiles as $profanitiesFile) {
                $words = array_merge(
                    $words,
                    include "$profanitiesDir/$profanitiesFile"
                );
            }
        }

        $words = array_merge($words, $including);

        $words = array_diff($words, $excluding);

        $fileContents = (string) file_get_contents($object->path);

        $russian = new Russian;

        $foundWords = array_filter($words, function (string $word) use ($fileContents, $russian): bool {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/i', $fileContents)) {
                return true;
            }

            if ($russian->is($word)) {
                $fileContents = Russian::normalize($fileContents);
                preg_match_all(Russian::pattern(), $fileContents, $matches);
            } else {
                preg_match_all('/[a-zA-Z]\w*/', $fileContents, $matches);
            }

            foreach ($matches[0] as $token) {
                $snakeParts = explode('_', $token);

                foreach ($snakeParts as $part) {
                    if (strcasecmp($part, $word) === 0) {
                        return true;
                    }
                }

                $camelParts = preg_split('/(?<!^)(?=[A-Z])/', $token);

                if (! is_array($camelParts)) {
                    return false;
                }

                foreach ($camelParts as $subpart) {
                    if (strcasecmp($subpart, $word) === 0) {
                        return true;
                    }
                }
            }

            return false;
        });

        if ($russian->isDetected()) {
            $foundWords = Russian::backToOrigin($foundWords);
        }

        return $foundWords === [];
    },
    function ($path) use (&$foundWords): string {
        return "to have no profanity, but found '".implode(', ', array_values($foundWords ?? []))."'";
    },
    FileLineFinder::where(function (string $line) use (&$foundWords): bool {
        return str_contains(strtolower($line), strtolower((string) array_values($foundWords ?? [])[0]));
    })
));
