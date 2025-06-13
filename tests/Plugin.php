<?php

use Pest\Profanity\Plugin;
use Symfony\Component\Console\Output\BufferedOutput;

test('output', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->toContain(
            '.. OK',
            '.. pr12(bollocks)',
            ' pr12(shit), pr14(shit), pr16(shit), pr18(shit)',
            '.. pr12(arse)',
            '.. pr12(fuck)',
            '.. pr12(nobhead)',
        )
        ->and($output->fetch())->not->toContain(
            '.. pr12(møgso)',
        );
});

test('compact output', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--compact']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->toContain(
            '.. pr12(bollocks)',
            ' pr12(shit), pr14(shit), pr16(shit), pr18(shit)',
            '.. pr12(arse)',
            '.. pr12(fuck)',
            '.. pr12(nobhead)',
        )
        ->and($output->fetch())->not->toContain(
            '.. OK',
            '.. pr12(møgso)'
        );
});

test('exclude word', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--exclude=fuck']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->not->toContain(
            '.. pr12(fuck)',
        );
});

test('include word', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--include=elephpant']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->toContain(
            ', pr20(elephpant)',
        );
});

test('specific language', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--language=da']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->toContain(
            '.. pr12(møgso)',
        )
        ->and($output->fetch())->not->toContain(
            '.. pr13(bollocks)'
        );
});

test('multiple languages', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--language=en,da']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->toContain(
            'pr12(møgso), pr13(bollocks)'
        );
});

test('config files return lowercase and unique values', function () {
    expect('src/Config')
        ->toReturnLowercase()
        ->toReturnUnique()
        ->toBeOrdered();
});

test('ignore lines', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity']))->toThrow(Exception::class, 1)
        ->and($output->fetch())->not->toContain(
            '.. pr12(shitty)'
        );
});

test('json output', function () {
    $output = new BufferedOutput;
    $plugin = new class($output) extends Plugin
    {
        public function exit(int $code): never
        {
            throw new Exception($code);
        }
    };

    expect(fn () => $plugin->handleOriginalArguments(['--profanity', '--compact', '--output=test.json']))->toThrow(Exception::class, 1)
        ->and(__DIR__.'/../test.json')->toBeReadableFile()
        ->and(file_get_contents(__DIR__.'/../test.json'))->json()->toMatchArray([
            'format' => 'pest',
            'result' => [
                [
                    'file' => 'tests/Fixtures/Properties.php',
                    'profanity' => [
                        'pr12(bollocks)',
                    ],
                ],
                [
                    'file' => 'tests/Fixtures/All.php',
                    'profanity' => [
                        'pr9(shit)',
                        'pr11(shit)',
                        'pr13(shit)',
                        'pr15(shit)',
                    ],
                ],
                [
                    'file' => 'tests/Fixtures/Comments.php',
                    'profanity' => [
                        'pr12(arse)',
                    ],
                ],
                [
                    'file' => 'tests/Fixtures/Constants.php',
                    'profanity' => [
                        'pr12(fuck)',
                    ],
                ],
                [
                    'file' => 'tests/Fixtures/Language.php',
                    'profanity' => [
                        'pr7(møgso)',
                        'pr8(bollocks)',
                    ],
                ],
                [
                    'file' => 'tests/Fixtures/Parameters.php',
                    'profanity' => [
                        'pr12(nobhead)',
                    ],
                ],
            ],
        ]);

    unlink(__DIR__.'/../test.json');
})->todo();
