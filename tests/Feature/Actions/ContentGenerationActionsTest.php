<?php

declare(strict_types=1);

use App\Actions\Content\GenerateStudyMaterialAction;
use App\Actions\Content\GenerateTestForSubjectAction;
use App\Models\Institution;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'IT School']);
    $this->subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Database Internals',
    ]);
});

test('generates study material for subject', function () {
    $action = app(GenerateStudyMaterialAction::class);

    $material = $action->execute($this->subject, 'laravel_eloquent');

    expect($material->subject_id)->toBe($this->subject->id);
    expect($material->title)->toBe('Dominando o Eloquent ORM no Laravel 13');
    expect($material->points_reward)->toBe(15);
    expect($material->content)->toContain('Eloquent ORM');
});

test('generates test and questions for subject', function () {
    $action = app(GenerateTestForSubjectAction::class);

    $test = $action->execute($this->subject, 'vue_composition');

    expect($test->subject_id)->toBe($this->subject->id);
    expect($test->title)->toBe('Avaliação: Reactividade e Estrutura no Vue 3');
    expect($test->points_reward)->toBe(50);
    expect($test->questions)->toHaveCount(3);

    $firstQuestion = $test->questions->first();
    expect($firstQuestion->question_text)->toContain('Como se acessa o valor de uma variável');
    expect($firstQuestion->options)->toBeArray();
    expect($firstQuestion->correct_option_index)->toBe(1);
});
