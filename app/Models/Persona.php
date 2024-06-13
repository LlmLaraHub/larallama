<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function wrapPromptInPersona(string $prompt): string
    {

        $title = $this->name;
        $persona = $this->content;
        $prompt = <<<PROMPT
$prompt

**IN THE PERSONA OF**
Name of Persona: $title
Example Content of Persona:
$persona
** END PERSONA EXAMPLE **
PROMPT;

        return $prompt;
    }
}
