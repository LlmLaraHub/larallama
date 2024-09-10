<?php

namespace Tests\Feature;

use App\Domains\Projects\DailyReportService;
use App\Models\Chat;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Notifications\DailyReport;
use Illuminate\Support\Facades\Notification;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class DailyReportServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_notify(): void
    {

        Notification::fake();

        $user = User::factory()->create();

        LlmDriverFacade::shouldReceive('driver->chat')
            ->once()
            ->andReturn(
                CompletionResponse::from([
                    'content' => 'Test Content',
                ])
            );

        $team = Team::factory()->create();
        $team->users()->attach($user);

        $project = Project::factory()->create([
            'end_date' => now()->addDays(7),
            'team_id' => $team->id,
        ]);

        $chat = Chat::factory()->withDrivers()->create([
            'chatable_id' => $project->id,
            'chatable_type' => Project::class,
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        (new DailyReportService())->handle();

        Notification::assertSentTo($user, DailyReport::class);

    }
}
