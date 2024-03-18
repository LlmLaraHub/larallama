<?php

namespace App\Http\Controllers;

use App\Domains\Examples\ExampleChartData;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ExampleChatBotController extends Controller
{
    public function show()
    {
        $exampleData = ExampleChartData::get();
        return inertia('Examples/ChatBot', [
            'example_data' => $exampleData
        ]);
    }

    public function chat() {
        $validated = request()->validate([
            'message' => 'required'
        ]);

        $mockedData = ExampleChartData::asJson();
        $prompt = "You are heling the user understand this data better. It is mock data we just want them to see how things work.
        The data is about compaings so the user might compare campaigns or just askin info about one campaign.
        Here is the mock data to help: $mockedData
        ";
        
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4-turbo-preview',
            'messages' => [
                [
                    'role' => 'system', 'content' => $prompt
                ],
                [
                    'role' => 'user', 'content' => $validated['message']
                ],
            ],
        ]);

        put_fixture("chat_response.json", $response->choices);

        return response()->json($response);
    }
}
