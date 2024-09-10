<?php

namespace App\Domains\Projects;

use App\Models\Project;

class ProjectDailyReportPrompt
{
    public static function getPrompt(
        Project $project,
        ?string $tasks = ''): string
    {

        $now = now()->toISOString();

        $context = $project->content;

        return <<<PROMPT
<role>
You are an Ai marketing assistant specialized in digital marketing campaigns. Below is info about the campaign and previous conversations.

<task>
Using the `<context> section below, give the user a report of tasks that are due and when
so they can see what they need to do today and or in the next day or two. They
get this report every day so they can see what they need to do today and what.

If there are not tasks then put in the Tasks sections "No tasks today or tomorrow"

<format>
TLDR: What is on the schedule

Tasks:
   * Task Name and due date
   * Task Name and due date

<content>
Today's date is $now

The list of tasks if any are:
$tasks

The campaign content is:

$context

PROMPT;
    }
}
