<?php

declare(strict_types=1);

use App\Jobs\GenerateContentJob;
use App\Models\Institution;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('GenerateContentJob generates study material and test for subject', function () {
    $institution = Institution::create(['name' => 'Test School']);
    $subject = Subject::create([
        'name' => 'Vue 3 Development',
        'institution_id' => $institution->id,
    ]);

    // Dispatch the job synchronously
    GenerateContentJob::dispatchSync($subject, 'Vue Composition API');

    $this->assertDatabaseHas('study_materials', [
        'subject_id' => $subject->id,
        'title' => 'Componentização Moderna com Vue 3 Composition API',
    ]);

    $this->assertDatabaseHas('tests', [
        'subject_id' => $subject->id,
        'title' => 'Avaliação: Reactividade e Estrutura no Vue 3',
    ]);
});
