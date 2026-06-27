<?php

use App\Services\MaterialGenerationService;

test('generates preset material details correctly', function () {
    $service = new MaterialGenerationService;

    // Eloquent
    $eloquent = $service->generateMaterialData('Laravel Eloquent');
    expect($eloquent['title'])->toBe('Dominando o Eloquent ORM no Laravel 13');
    expect($eloquent['content'])->toContain('N+1');

    // Vue
    $vue = $service->generateMaterialData('VueJS composition');
    expect($vue['title'])->toBe('Componentização Moderna com Vue 3 Composition API');

    // Tailwind
    $tailwind = $service->generateMaterialData('Tailwind CSS layout');
    expect($tailwind['title'])->toBe('Layouts Flexíveis e Responsivos com Tailwind CSS');
});

test('generates fallback generic material details for unknown themes', function () {
    $service = new MaterialGenerationService;

    $generic = $service->generateMaterialData('Continuous Integration');

    expect($generic['title'])->toBe('Estudo Aprofundado: Continuous Integration');
    expect($generic['content'])->toContain('Continuous Integration');
});

test('generates preset test data correctly', function () {
    $service = new MaterialGenerationService;

    $eloquentTest = $service->generateTestData('Laravel Eloquent');
    expect($eloquentTest['title'])->toBe('Avaliação: Eloquent ORM e Relacionamentos');
    expect($eloquentTest['questions'])->toHaveCount(3);
    expect($eloquentTest['questions'][0]['correct_option_index'])->toBe(1);
});

test('generates fallback test data for unknown themes', function () {
    $service = new MaterialGenerationService;

    $genericTest = $service->generateTestData('Kubernetes Deployments');

    expect($genericTest['title'])->toBe('Quiz Rápido: Kubernetes Deployments');
    expect($genericTest['questions'])->toHaveCount(2);
});
