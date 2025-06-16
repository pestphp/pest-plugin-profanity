<?php

declare(strict_types=1);

namespace Pest\Profanity;

use Pest\Contracts\Plugins\HandlesOriginalArguments;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Profanity\Contracts\Logger;
use Pest\Profanity\Logging\JsonLogger;
use Pest\Profanity\Logging\NullLogger;
use Pest\Profanity\Support\ConfigurationSourceDetector;
use Pest\TestSuite;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use function Termwind\render;
use function Termwind\renderUsing;
use function Termwind\terminal;

/**
 * @internal
 */
class Plugin implements HandlesOriginalArguments
{
    use HandleArguments;

    /**
     * @var array<string>
     */
    private array $excludeWords = [];

    /**
     * @var array<string>
     */
    private array $includeWords = [];

    /**
     * @var string|array<string>|null
     */
    private $language = null;

    private bool $compact = false;

    /**
     * The logger used to output profanity to a file.
     */
    private Logger $profanityLogger;

    /**
     * Creates a new Plugin instance.
     */
    public function __construct(
        private readonly OutputInterface $output
    ) {
        $this->profanityLogger = new NullLogger;
    }

    /**
     * Validates that the specified languages exist in the profanities directory.
     *
     * @param  array<string>|null  $languages
     * @return array<int, string> List of languages that don't exist
     */
    private function validateLanguages($languages): array
    {
        if ($languages === null) {
            return [];
        }

        $profanitiesDir = __DIR__.'/Config/profanities';
        $invalidLanguages = [];

        foreach ($languages as $language) {
            $specificLanguage = "$profanitiesDir/$language.php";
            if (! file_exists($specificLanguage)) {
                $invalidLanguages[] = $language;
            }
        }

        return $invalidLanguages;
    }

    /**
     * {@inheritdoc}
     */
    public function handleOriginalArguments(array $arguments): void
    {
        if (! $this->hasArgument('--profanity', $arguments)) {
            return;
        }

        foreach ($arguments as $key => $argument) {
            if (str_starts_with($argument, '--exclude=')) {
                $words = explode(',', substr($argument, strlen('--exclude=')));
                $this->excludeWords = array_merge($this->excludeWords, $words);
                unset($arguments[$key]);
            }

            if (str_starts_with($argument, '--include=')) {
                $words = explode(',', substr($argument, strlen('--include=')));
                $this->includeWords = array_merge($this->includeWords, $words);
                unset($arguments[$key]);
            }

            if (str_starts_with($argument, '--language=')) {
                $languageValue = substr($argument, strlen('--language='));
                $this->language = explode(',', $languageValue);
                unset($arguments[$key]);
            }

            if (str_starts_with($argument, '--compact')) {
                $this->compact = true;
                unset($arguments[$key]);
            }

            if (str_starts_with($argument, '--output=')) {
                $outputPath = explode('=', $argument)[1] ?? null;

                if (empty($outputPath)) {
                    render(<<<'HTML'
                        <div class="my-1">
                            <span class="ml-2 px-1 bg-red font-bold">ERROR</span>
                            <span class="ml-1">
                                No output path provided for [--profanity-json].
                            </span>
                        </div>
                    HTML);

                    $this->exit(1);
                }

                $this->profanityLogger = new JsonLogger(explode('=', $argument)[1]);
            }
        }

        $invalidLanguages = $this->validateLanguages($this->language);
        if (! empty($invalidLanguages)) {
            $invalidLangsStr = implode(', ', $invalidLanguages);
            render(<<<HTML
                <div class="my-1">
                    <span class="ml-2 px-1 bg-red font-bold">ERROR</span>
                    <span class="ml-1">
                        The specified language does not exist: {$invalidLangsStr}
                    </span>
                </div>
            HTML);

            $this->output->writeln(['']);
            $this->output->writeln('<info>Available languages:</info>');

            $profanitiesDir = __DIR__.'/Config/profanities';
            $availableLanguages = array_map(
                fn ($file) => basename($file, '.php'),
                glob("$profanitiesDir/*.php")
            );

            $this->output->writeln(implode(', ', $availableLanguages));
            $this->output->writeln(['']);
            $this->exit(1);
        }

        $source = ConfigurationSourceDetector::detect();

        if ($source === []) {
            render(<<<'HTML'
                <div class="my-1">
                    <span class="ml-2 px-1 bg-red font-bold">ERROR</span>
                    <span class="ml-1">
                        No source section found. Did you forget to add a `source` section to your `phpunit.xml` file?
                    </span>
                </div>
            HTML);

            $this->exit(1);
        }

        $files = Finder::create()
            ->in($source)
            ->name('*.php')
            ->notPath('Config/profanities')
            ->notPath('src/Config/profanities')
            ->files();
        $filesWithProfanity = [];
        $totalProfanities = 0;

        $this->output->writeln(['']);

        Analyser::analyse(
            array_keys(iterator_to_array($files)),
            function (Result $result) use (&$filesWithProfanity, &$totalProfanities): void {
                $path = str_replace(TestSuite::getInstance()->rootPath.'/', '', $result->file);
                $errors = $result->errors;

                $truncateAt = max(1, terminal()->width() - 24);

                if (empty($errors)) {
                    if (! $this->compact) {
                        renderUsing($this->output);
                        render(<<<HTML
                        <div class="flex mx-2">
                            <span class="truncate-{$truncateAt}">{$path}</span>
                            <span class="flex-1 content-repeat-[.] text-gray mx-1"></span>
                            <span class="text-green">OK</span>
                        </div>
                        HTML);

                        $this->profanityLogger->append($path, []);
                    }
                } else {
                    $filesWithProfanity[] = $path;
                    $totalProfanities += count($errors);

                    usort($errors, fn ($a, $b): int => $a->line <=> $b->line);

                    $profanityLines = [];
                    foreach ($errors as $error) {
                        $profanityLines[] = $error->getShortType().$error->line.'('.$error->word.')';
                    }

                    $this->profanityLogger->append($path, $profanityLines);

                    $profanityLines = implode(', ', $profanityLines);

                    renderUsing($this->output);
                    render(<<<HTML
                    <div class="flex mx-2">
                        <span class="truncate-{$truncateAt}">{$path}</span>
                        <span class="flex-1 content-repeat-[.] text-gray mx-1"></span>
                        <span class="text-red">{$profanityLines}</span>
                    </div>
                    HTML);
                }
            },
            $this->excludeWords,
            $this->includeWords,
            $this->language
        );

        $filesWithProfanityCount = count($filesWithProfanity);
        $exitCode = (int) (! empty($filesWithProfanity));

        $this->profanityLogger->output();

        if ($exitCode === 1) {
            render(<<<HTML
                <div class="my-1">
                    <span class="ml-2 px-1 bg-red font-bold">ERROR</span>
                    <span class="ml-1">
                        Found {$totalProfanities} instances of profanity in {$filesWithProfanityCount} files
                    </span>
                </div>
            HTML);
        } else {
            render(<<<'HTML'
                <div class="my-1">
                    <span class="ml-2 px-1 bg-green font-bold">PASS</span>
                    <span class="ml-1">
                        No profanity found in your application!
                    </span>
                </div>
            HTML);
        }

        $this->output->writeln(['']);
        $this->exit($exitCode);
    }

    /**
     * Exits the process with the given code.
     */
    public function exit(int $code): never
    {
        exit($code);
    }
}
