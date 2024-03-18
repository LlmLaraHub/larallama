<?php

namespace App\Http\Controllers;

use OpenAI\Laravel\Facades\OpenAI;

class ExampleController extends Controller
{
    public function charts()
    {
        // //Request = "Give me info about campaign 1 and campaign 2"

        // //$prompt: As a helpful assitant using the confines of the data we have for that campaign please looking that info about each campaign then
        // // combine the data into a single chart that shows the performance of each campaign over time.

        // //** Earnest */
        // $info = OpenAI::completion()->create([
        //     'model' => 'text-davinci-003',
        //     'prompt' => $prompt,
        //     'max_tokens' => 50,
        // ]);

        // //Response:
        // // = Campaign 1 has a higher click through rate than campaign 2.

        return inertia('Examples/Charts');
    }
}
