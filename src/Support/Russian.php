<?php

declare(strict_types=1);

namespace JonPurvis\Profanify\Support;

final class Russian
{
    public static function is(string $text): bool
    {
        return (bool) preg_match('/[А-Яа-яЁё]/u', $text);
    }

    public static function normalize(string $text): string
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
}
