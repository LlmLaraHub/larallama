<?php

namespace App\Domains\Tokenizer;

class Templatizer
{
    protected string $content;

    protected array $token;

    protected ?string $replacement;

    protected bool $appendContext = false;

    public static function getTokens(): array
    {
        $reflection = new \ReflectionClass(Templatizer::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        return array_values(array_filter(array_map(function ($method) {
            $name = $method->getName();
            if (! in_array($name, ['makeMethod', 'handle', 'getTokens'])) {
                return strtoupper($name);
            }

            return null;
        }, $methods)));
    }

    public function handle(
        string $content,
        ?string $replacement = null): string
    {

        if ($this->appendContext && ! str($content)->contains('[CONTEXT]')) {
            $content = str($content)->append(' [CONTEXT]')->toString();
        }

        $this->content = $content;
        $this->replacement = $replacement;

        foreach ($this->getTokens() as $token) {
            $method = $this->makeMethod($token);
            if (method_exists($this, $method)) {
                $this->{$method}();
            }
        }

        return $this->content;

    }

    protected function makeMethod(string $token): string
    {
        return str($token)->remove(['[', ']'])->lower()->snake()->toString();
    }

    protected function year()
    {
        $replacement = now()->format('Y');

        $this->content =
            str($this->content)
                ->replace('[YEAR]', $replacement)
                ->toString();
    }

    protected function end_week()
    {
        $replacement = now()->endOfWeek()->format('m/d/Y');
        $replacement = $replacement.' '.now()->endOfWeek()->format('M d, Y');
        $this->content =
            str($this->content)
                ->replace('[END_WEEK]', $replacement)
                ->toString();
    }

    public function appendContext(bool $appendContext = false): self
    {
        $this->appendContext = $appendContext;

        return $this;
    }

    protected function start_week(): void
    {
        $replacement = now()->startOfWeek()->format('m/d/Y');
        $replacement = $replacement.' '.now()->startOfWeek()->format('M d, Y');
        $this->content =
            str($this->content)
                ->replace('[START_WEEK]', $replacement)
                ->toString();
    }

    protected function next_month(): void
    {
        $replacement = 'Month of '.now()->addMonth()->format('m ');
        $replacement = $replacement.' '.now()->addMonth()->format('M');
        $this->content =
            str($this->content)
                ->replace('[NEXT_MONTH]', $replacement)
                ->toString();
    }

    protected function current_month(): void
    {
        $replacement = 'Month of '.now()->format('m');
        $replacement = $replacement.' '.now()->format('M');
        $this->content =
            str($this->content)
                ->replace('[CURRENT_MONTH]', $replacement)
                ->toString();
    }

    protected function today(): void
    {
        $replacement = now()->format('m/d/Y');
        $replacement = $replacement.' '.now()->format('M d, Y');
        $this->content =
            str($this->content)
                ->replace('[START_WEEK]', $replacement)
                ->toString();
    }

    protected function user_input(): void
    {
        $this->content = str($this->content)->replace('[USER_INPUT]', $this->replacement)->toString();
    }

    protected function context(): void
    {
        if (! empty($this->replacement)) {
            $this->content = str($this->content)->replace('[CONTEXT]', $this->replacement)->toString();
        }
    }
}
