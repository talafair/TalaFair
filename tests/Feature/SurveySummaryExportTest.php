<?php

namespace Tests\Feature;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveySummaryExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_can_view_detailed_survey_summary_with_responses(): void
    {
        /** @var User $official */
        $official = User::factory()->create([
            'role' => 'official',
            'official_group' => 'barangay',
            'official_position' => 'captain',
        ]);

        $survey = Survey::create([
            'title' => 'Community event feedback',
            'description' => 'Tell us how the event went.',
            'due_at' => now()->addDays(7),
            'points' => 10,
            'questions' => ['Question one', 'Question two'],
            'suggestion_enabled' => true,
            'audience' => 'public',
            'created_by' => $official->id,
        ]);

        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $official->id,
            'answers' => [5, 3],
            'suggestion' => 'Great event',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($official)->get(route('surveys.manage'));

        $response->assertOk();
        $response->assertSeeText('Overall average / 5');
        $response->assertSeeText('Question one');
        $response->assertSeeText('4.00');
        $response->assertSeeText('Survey responses');
        $response->assertDontSee('Download DOC');
    }
}
