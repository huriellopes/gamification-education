<?php

declare(strict_types=1);

use App\Services\System\LogFileService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->logPath = storage_path('logs');

    File::ensureDirectoryExists($this->logPath);

    // Arquivo de log legítimo (o único que deveria ser lido).
    File::put($this->logPath . '/laravel.log', "linha 1\nlinha 2\n");

    // Arquivo sensível fora do allowlist — não é retornado por list(), mas
    // sozinho o basename() não impediria a leitura dele.
    File::put($this->logPath . '/secret-not-a-log.txt', 'segredo');

    $this->service = new LogFileService;
});

afterEach(function () {
    File::delete([
        $this->logPath . '/laravel.log',
        $this->logPath . '/secret-not-a-log.txt',
    ]);
});

test('reads a known log file from the allowlist', function () {
    $result = $this->service->read('laravel.log');

    expect($result)->not->toBeNull();
    expect($result['name'])->toBe('laravel.log');
});

test('refuses to read a file that is not in the allowlist even without traversal', function () {
    expect($this->service->read('secret-not-a-log.txt'))->toBeNull();
});

test('refuses to read a file outside storage/logs via path traversal', function () {
    expect($this->service->read('../../.env'))->toBeNull();
});

test('returns null for an empty or missing file name', function () {
    expect($this->service->read(null))->toBeNull();
    expect($this->service->read(''))->toBeNull();
    expect($this->service->read('does-not-exist.log'))->toBeNull();
});
