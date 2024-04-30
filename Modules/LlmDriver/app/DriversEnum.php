<?php

namespace LlmLaraHub\LlmDriver;

enum DriversEnum: string
{
    case Mock = 'mock';
    case OpenAi = 'openai';
    case OpenAiAzure = 'openai_azure';
    case Ollama = 'ollama';
    case Gemini = 'gemini';
    case Claude = 'claude';
    case Groq = 'groq';
}
