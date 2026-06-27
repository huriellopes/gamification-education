<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\StudyMaterial;
use App\Models\TestAttempt;
use App\Actions\CompleteStudyMaterialAction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudyController extends Controller
{
    /**
     * Exibe a trilha de aprendizado de uma matéria (materiais e testes).
     */
    public function show(Subject $subject)
    {
        $user = Auth::user();

        // Carrega materiais de estudo e verifica se cada um foi completado pelo aluno
        $materials = $subject->studyMaterials()
            ->get()
            ->map(function ($material) use ($user) {
                $completed = $user->completedMaterials()
                    ->where('study_material_id', $material->id)
                    ->exists();

                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'points_reward' => $material->points_reward,
                    'completed' => $completed,
                ];
            });

        // Carrega testes e as melhores pontuações do aluno em cada um
        $tests = $subject->tests()
            ->get()
            ->map(function ($test) use ($user) {
                $bestAttempt = TestAttempt::where('user_id', $user->id)
                    ->where('test_id', $test->id)
                    ->orderBy('score', 'desc')
                    ->first();

                return [
                    'id' => $test->id,
                    'title' => $test->title,
                    'description' => $test->description,
                    'points_reward' => $test->points_reward,
                    'best_score' => $bestAttempt ? $bestAttempt->score : null,
                    'correct_answers' => $bestAttempt ? $bestAttempt->correct_answers : null,
                    'total_questions' => $bestAttempt ? $bestAttempt->total_questions : null,
                ];
            });

        return Inertia::render('Student/Subjects/Show', [
            'subject' => $subject,
            'materials' => $materials,
            'tests' => $tests,
        ]);
    }

    /**
     * Exibe os detalhes de um material de estudo para leitura.
     */
    public function showMaterial(Subject $subject, StudyMaterial $material)
    {
        // Garante que o material pertence à matéria
        abort_if($material->subject_id !== $subject->id, 404);

        $user = Auth::user();
        $completed = $user->completedMaterials()
            ->where('study_material_id', $material->id)
            ->exists();

        return Inertia::render('Student/Materials/Show', [
            'subject' => $subject,
            'material' => $material,
            'completed' => $completed,
        ]);
    }

    /**
     * Marca o material de estudo como concluído pelo aluno, gerando pontuações.
     */
    public function completeMaterial(Subject $subject, StudyMaterial $material, CompleteStudyMaterialAction $action)
    {
        abort_if($material->subject_id !== $subject->id, 404);

        $user = Auth::user();
        $success = $action->execute($user, $material);

        if ($success) {
            return redirect()->route('student.subjects.show', $subject)
                ->with('success', "Leitura concluída! Você ganhou +{$material->points_reward} pontos!");
        }

        return redirect()->route('student.subjects.show', $subject)
            ->with('info', "Você já havia concluído este material anteriormente.");
    }
}
