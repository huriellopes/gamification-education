<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\StudyMaterial;
use App\Models\Test;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\ScoreHistory;
use App\Actions\CompleteStudyMaterialAction;
use App\Actions\SubmitTestAttemptAction;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Subject $subject;
    private StudyMaterial $material;
    private Test $test;
    private Question $q1;
    private Question $q2;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração básica do cenário de testes
        $institution = Institution::create([
            'name' => 'Test Institution',
            'description' => 'Description'
        ]);

        $this->student = User::create([
            'name' => 'Alice Student',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'points' => 0,
            'institution_id' => $institution->id
        ]);

        $this->subject = Subject::create([
            'institution_id' => $institution->id,
            'name' => 'Laravel Advanced',
            'description' => 'Description'
        ]);

        $this->material = StudyMaterial::create([
            'subject_id' => $this->subject->id,
            'title' => 'Introduction to Actions',
            'content' => 'Markdown content',
            'points_reward' => 15
        ]);

        $this->test = Test::create([
            'subject_id' => $this->subject->id,
            'title' => 'Actions Assessment',
            'description' => 'Test descriptions',
            'points_reward' => 50
        ]);

        $this->q1 = Question::create([
            'test_id' => $this->test->id,
            'question_text' => 'What is the Single Responsibility Principle?',
            'options' => ['A class should have only one reason to change', 'A class should have multiple reasons to change'],
            'correct_option_index' => 0
        ]);

        $this->q2 = Question::create([
            'test_id' => $this->test->id,
            'question_text' => 'Where are actions located?',
            'options' => ['app/Models', 'app/Actions'],
            'correct_option_index' => 1
        ]);
    }

    /**
     * Testa a conclusão de leitura do material de estudo.
     */
    public function test_student_can_complete_study_material_and_earn_points(): void
    {
        $action = app(CompleteStudyMaterialAction::class);
        
        $result = $action->execute($this->student, $this->material);

        $this->assertTrue($result);
        $this->student->refresh();
        
        // Verifica se os pontos foram creditados
        $this->assertEquals(15, $this->student->points);

        // Verifica o histórico de extrato de pontos
        $this->assertDatabaseHas('score_histories', [
            'user_id' => $this->student->id,
            'points' => 15,
            'source_type' => 'material',
            'source_id' => $this->material->id,
        ]);

        // Tenta completar novamente e não deve pontuar em duplicidade
        $secondAttemptResult = $action->execute($this->student, $this->material);
        $this->assertFalse($secondAttemptResult);
        $this->student->refresh();
        $this->assertEquals(15, $this->student->points); // Continua com 15
    }

    /**
     * Testa a correção de tentativas de testes e o cálculo de pontos.
     */
    public function test_student_can_submit_test_attempt_and_earn_points_proportional(): void
    {
        $action = app(SubmitTestAttemptAction::class);

        // Aluno responde 1 correta de 2 (50% de acertos)
        // Pontos proporcionais: 50% de 50 pts = 25 pts
        $answers = [
            $this->q1->id => 0, // Correta (0)
            $this->q2->id => 0, // Errada (deveria ser 1)
        ];

        $attempt = $action->execute($this->student, $this->test, $answers);

        $this->assertEquals(25, $attempt->score);
        $this->assertEquals(1, $attempt->correct_answers);
        $this->assertEquals(2, $attempt->total_questions);

        $this->student->refresh();
        $this->assertEquals(25, $this->student->points);

        // Segunda tentativa com melhora da nota: responde tudo correto (100% de acertos)
        // Pontos proporcionais: 100% de 50 pts = 50 pts
        // Deve receber a diferença (+25 pts)
        $newAnswers = [
            $this->q1->id => 0, // Correta
            $this->q2->id => 1, // Correta
        ];

        $newAttempt = $action->execute($this->student, $this->test, $newAnswers);

        $this->assertEquals(50, $newAttempt->score);
        $this->student->refresh();
        $this->assertEquals(50, $this->student->points); // 25 + 25 de diferença = 50 pts

        // Terceira tentativa com piora da nota: responde 0 corretas
        // Placar: 0 pts, melhor score anterior: 50 pts. Não deve alterar pontos
        $badAnswers = [
            $this->q1->id => 1,
            $this->q2->id => 0,
        ];

        $badAttempt = $action->execute($this->student, $this->test, $badAnswers);

        $this->assertEquals(0, $badAttempt->score);
        $this->student->refresh();
        $this->assertEquals(50, $this->student->points); // Mantém o melhor score
    }

    /**
     * Testa os serviços de ranking.
     */
    public function test_ranking_service_returns_correctly(): void
    {
        $rankingService = app(RankingService::class);

        // Criar outro aluno
        $student2 = User::create([
            'name' => 'Bob Student',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'points' => 120,
            'institution_id' => $this->student->institution_id
        ]);

        $this->student->update(['points' => 80]);

        $ranking = $rankingService->getGlobalRanking();

        // Bob (120 pts) deve estar em 1º, Alice (80 pts) em 2º
        $this->assertEquals('Bob Student', $ranking[0]->name);
        $this->assertEquals('Alice Student', $ranking[1]->name);
    }
}
