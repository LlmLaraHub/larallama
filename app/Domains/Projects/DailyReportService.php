<?php

namespace App\Domains\Projects;

use App\Domains\Messages\RoleEnum;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\DailyReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class DailyReportService
{
    public function handle(): void
    {
        foreach (Project::active()->get() as $project) {
            $this->sendReport($project);
        }
    }

    public function sendReport(Project $project)
    {
        $tasks = Task::where('project_id', $project->id)
            ->notCompleted()
            ->where('due_date', '>=', now()->addDays(7))
            ->get()
            /** @phpstan-ignore-next-line */
            ->transform(function (Task $item) {
                return sprintf(
                    'Task: %s %s %s',
                    $item->name,
                    $item->details,
                    $item->due_date
                );
            })->implode(', ');

        $prompt = ProjectDailyReportPrompt::getPrompt($project, $tasks);

        $project->getChat()->addInput(
            message: $prompt,
            role: RoleEnum::User,
        );

        $messages = $project->getChat()->getMessageThread();

        $results = LlmDriverFacade::driver($project->getDriver())
            ->setToolType(ToolTypes::Chat)
            ->chat($messages);

        $project->getChat()->addInput(
            message: $results->content,
            role: RoleEnum::Assistant,
        );

        Log::info('DailyReportService::handle', [
            'results' => $results->content,
        ]);

        Notification::send($project->team->allUsers(), new DailyReport($results->content, $project));
    }
}
