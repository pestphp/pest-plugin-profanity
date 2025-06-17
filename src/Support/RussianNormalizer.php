<?php
function normalizeRussianText(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace('ё', 'е', $text);

    $latinToCyrillic = [
        'a' => 'а',
        'c' => 'с',
        'e' => 'е',
        'o' => 'о',
        'p' => 'р',
        'x' => 'х',
        'y' => 'у',
        'b' => 'б',
        'm' => 'м',
        'h' => 'н',
        'k' => 'к',
        't' => 'т',
        'B' => 'В',
        'D' => 'Д',
        'H' => 'Н',
        'K' => 'К',
        'M' => 'М',
        'O' => 'О',
        'P' => 'Р',
        'C' => 'С',
        'T' => 'Т',
        'X' => 'Х',
        'Y' => 'У'
    ];
    $latinToCyrillic += [
        'b' => 'б', 'd' => 'д', 'f' => 'ф', 'g' => 'г', 'i' => 'и', 'j' => 'ј', 'l' => 'л', 'n' => 'п', 'q' => 'қ', 'v' => 'в', 'w' => 'ш', 'u' => 'u'
    ];
    $text = strtr($text, $latinToCyrillic);

    $charSubstitutions = [
        '@' => 'а',
        '€' => 'е',
        '£' => 'л',
        '₽' => 'р',
        '0' => 'о',
        '3' => 'з',
        '4' => 'ч',
        '6' => 'б',
        '1' => 'л',
        '$' => 's',
        '|' => 'л',
        '!' => 'і',
        '?' => '',
        '*' => '',
        '.' => '',
        ',' => '',
        '-' => '',
        '_' => '',
        '+' => '',
        '=' => '',
        '/' => '',
        '\\' => '',
        '"' => '',
        '\''=> '',
        ':' => '',
        ';' => '',
        '~' => '',
        '`' => '',
        '^' => '',
    ];
    $text = strtr($text, $charSubstitutions);

    $text = preg_replace('/[^\\p{L}\\p{N}]+/u', '', $text);

    return $text;
}

function filterRussianProfanity(string $text, string $path = ''): ?array
{
    $profanities = include __DIR__ . '/ru.php';
    $normalizedText = normalizeRussianText($text);

    $found = [];
    foreach ($profanities as $badWord) {
        if ($badWord === '') {
            continue;
        }
        if (mb_strpos($normalizedText, $badWord) !== false) {
            $found[] = $badWord;
        }
    }
    if (!empty($found)) {
        $uniqueWords = array_unique($found);
        $message = "Profanity detected";
        if ($path !== '') {
            $message .= " in file '{$path}'";
        }
        $message .= ": [" . implode(', ', $uniqueWords) . "]";

        error_log($message);
        return $uniqueWords;
    }
    return null;
}

function assertNoRussianProfanity(string $filePath)
{
    $lines = file($filePath);
    $badWords = include __DIR__ . '/ru.php';
    $offenses = [];

    foreach ($lines as $num => $line) {
        $normalizedLine = normalizeRussianText($line);
        foreach ($badWords as $bad) {
            if ($bad !== '' && mb_strpos($normalizedLine, $bad) !== false) {
                $offenses[] = $num + 1;
                break;
            }
        }
    }

    if (!empty($offenses)) {
        $lineList = implode(', ', $offenses);
        $message = "Expecting '{$filePath}' to not use profanity.\nat {$filePath}:{$lineList}";
        throw new \Exception($message);
    }
}
