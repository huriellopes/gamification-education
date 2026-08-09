<?php

declare(strict_types=1);

use App\Support\SensitiveDataRedactor;

test('redacts known sensitive keys at the top level', function () {
    $redacted = SensitiveDataRedactor::redact([
        'id' => 1,
        'name' => 'Aluno',
        'password' => '$2y$12$abcdefghijklmnopqrstuv',
        'remember_token' => 'some-token',
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_recovery_codes' => ['AAAA-1111', 'BBBB-2222'],
    ]);

    expect($redacted)->toBe([
        'id' => 1,
        'name' => 'Aluno',
        'password' => '[redacted]',
        'remember_token' => '[redacted]',
        'two_factor_secret' => '[redacted]',
        'two_factor_recovery_codes' => '[redacted]',
    ]);
});

test('redacts sensitive keys nested inside other arrays', function () {
    $redacted = SensitiveDataRedactor::redact([
        'model' => 'App\\Models\\User',
        'values' => [
            'name' => 'Aluno',
            'password' => 'hash-aqui',
        ],
    ]);

    expect($redacted['values']['password'])->toBe('[redacted]')
        ->and($redacted['values']['name'])->toBe('Aluno');
});

test('preserves null instead of pretending a secret existed', function () {
    $redacted = SensitiveDataRedactor::redact([
        'password' => null,
        'two_factor_secret' => null,
    ]);

    expect($redacted['password'])->toBeNull()
        ->and($redacted['two_factor_secret'])->toBeNull();
});

test('leaves ordinary data completely untouched', function () {
    $data = [
        'id' => 42,
        'name' => 'Matéria X',
        'is_active' => true,
        'nested' => ['title' => 'Sub', 'points_reward' => 10],
    ];

    expect(SensitiveDataRedactor::redact($data))->toBe($data);
});
