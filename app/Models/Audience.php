<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function wrapPromptInAudience(string $prompt): string
    {

        $title = $this->name;
        $audience = $this->content;

        return <<<PROMPT
$prompt

**AUDIENCE INFO**
Name of Audience: $title
Details ofAudience:
$audience
**AUDIENCE INFO**
PROMPT;

    }
}
