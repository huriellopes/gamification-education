<?php

declare(strict_types=1);

namespace App\Http\Resources\SuperAdmin;

use App\Models\Institution;
use App\Models\Question;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subject
 */
class SubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration' => $this->duration,
            'institution_id' => $this->institution_id,
            'institution' => $this->whenLoaded('institution', function () {
                /** @var Institution $institution */
                $institution = $this->institution;

                return [
                    'id' => $institution->id,
                    'name' => $institution->name,
                ];
            }),
            'is_active' => $this->is_active,
            'study_materials' => $this->whenLoaded('studyMaterials', function () {
                /** @var Collection<int, StudyMaterial> $materials */
                $materials = $this->studyMaterials;

                return $materials->map(function (StudyMaterial $mat) {
                    return [
                        'id' => $mat->id,
                        'subject_id' => $mat->subject_id,
                        'title' => $mat->title,
                        'content' => $mat->content,
                        'points_reward' => $mat->points_reward,
                    ];
                })->all();
            }),
            'tests' => $this->whenLoaded('tests', function () {
                /** @var Collection<int, Test> $tests */
                $tests = $this->tests;

                return $tests->map(function (Test $test) {
                    /** @var Collection<int, Question> $questions */
                    $questions = $test->questions;

                    return [
                        'id' => $test->id,
                        'subject_id' => $test->subject_id,
                        'title' => $test->title,
                        'description' => $test->description,
                        'points_reward' => $test->points_reward,
                        'questions' => $questions->map(function (Question $q) {
                            return [
                                'id' => $q->id,
                                'test_id' => $q->test_id,
                                'question_text' => $q->question_text,
                                'options' => $q->options,
                                'correct_option_index' => $q->correct_option_index,
                            ];
                        })->all(),
                    ];
                })->all();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
