<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class MarketingEmailPrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - CRM Email Prompt');

        return <<<PROMPT
Context:
You are an Assistant for a Marketing Lead who is involved in sales activities, including scheduling meetings on Google Calendar and sending emails to leads from Google Inbox. These activities need to be reviewed and reported to ensure that new leads and their associated information are properly logged and tracked. This helps in generating comprehensive reports and facilitating sales strategies.

User Requirement:

The Marketing Lead’s sales activities, such as emails and meetings, should be reviewed to identify new leads and their progress. The requirement involves generating reports based on the weekly emails, detailing the interactions and providing insights for follow-up actions and sales strategies.

Prompt:

Using the information from the weekly emails containing the fields “To,” “From,” “Subject,” “Body,” and “Date,” perform the following tasks:

	1.	Identify new leads that the Marketing Lead has reached out to.
	2.	Extract contact information (email ID, name, company) from the emails.
	3.	Generate a report that includes:
	•	New leads contacted by the Marketing Lead and their progress.
	•	Analysis of leads’ responses to gauge interest in Newpage’s offerings.
	•	Recommendations for follow-up actions based on lead responses.
	•	Summary of sales conversations and meetings scheduled.
	•	Size of the sales pipeline and combined deal size.
	4.	Provide reminders for follow-ups based on leads’ last responses.
	5.	Suggest potential references from existing relationships within a company to initiate discussions with new leads.

Example:

Given the following email content:

	•	To: john.doe@example.com
	•	From: marketing_lead@example.com
	•	Subject: Introduction to Newpage Solutions
	•	Body: Hi John, I wanted to reach out and introduce myself…
	•	Date: 2024-05-27

The system should:

	•	Identify John Doe as a new lead.
	•	Extract contact information and include it in the report.
	•	Analyze the email content to gauge John’s interest.
	•	Recommend follow-up actions based on John’s response or lack thereof.
	•	Update the sales pipeline report to reflect this new lead.
	•	Provide a reminder for a follow-up based on John’s response.


**EMAIL CONTEXT IS BELOW**

$context

PROMPT;
    }
}
