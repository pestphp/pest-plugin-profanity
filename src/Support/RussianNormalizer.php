<?php

declare(strict_types=1);

namespace JonPurvis\Profanify\Support;

final class RussianNormalizer
{
    public static function normalizeRussianText(string $text): string
    {
        $text = mb_strtolower(str_replace('ё', 'е', $text), 'UTF-8');

        $text = strtr($text, [
            '@' => 'а', '0' => 'о', '1' => 'и', '3' => 'з', '4' => 'ч', '6' => 'б',
            'a' => 'а', 'c' => 'с', 'e' => 'е', 'o' => 'о', 'p' => 'р', 'x' => 'х', 'y' => 'й', 'k' => 'к',
            'b' => 'б', 'd' => 'д', 'g' => 'г', 'h' => 'н', 'm' => 'м', 't' => 'т', 'v' => 'в', 'i' => 'и',
            '|' => 'л', '!' => 'и', '_' => '', '-' => '', '*' => '', '.' => '', ',' => '',
        ]);

        return preg_replace('/[^а-я]+/u', '', $text) ?: '';
    }

    /**
     * @return array<int, string>|null
     */
    public static function filterRussianProfanity(string $text): ?array
    {
        /** @var array<int, string> $profanities */
        $profanities = include __DIR__.'/../Config/profanities/ru.php';

        $normalized = self::normalizeRussianText($text);
        $found = [];

        foreach ($profanities as $bad) {
            if ($bad !== '' && str_contains($normalized, (string) $bad)) {
                $found[] = $bad;
            }
        }

        return $found !== [] ? $found : null;
    }

    public static function assertNoRussianProfanity(string $filePath): void
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES) ?: [];
        /** @var array<int, string> $badWords */
        $badWords = include __DIR__.'/../Config/profanities/ru.php';

        $offended = [];

        foreach ($lines as $num => $line) {
            $norm = self::normalizeRussianText($line);

            foreach ($badWords as $bad) {
                if ($bad !== '' && str_contains($norm, (string) $bad)) {
                    $offended[] = $num + 1;
                    break;
                }
            }
        }

        if ($offended !== []) {
            throw new \Exception(
                sprintf('Profanity in %s, lines: %s', $filePath, implode(', ', $offended)),
            );
        }
    }
}
