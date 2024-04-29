<?php

namespace App\Domains\Agents;

abstract class BaseAgent
{
    abstract public function verify(VerifyPromptInputDto $input): VerifyPromptOutputDto;
}
