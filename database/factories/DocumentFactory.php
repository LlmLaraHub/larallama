<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TypesEnum::random(),
            'status' => StatusEnum::random(),
            'source_id' => Source::factory(),
            'meta_data' => [],
            'status_summary' => StatusEnum::random(),
            'link' => $this->faker->url(),
            'summary' => $this->faker->text(),
            'subject' => $this->faker->text(),
            'child_type' => StructuredTypeEnum::Narrative,
            'file_path' => $this->faker->url(),
            'document_chunk_count' => $this->faker->numberBetween(1, 10),
            'collection_id' => Collection::factory(),
        ];
    }

    public function pdf(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TypesEnum::PDF,
            ];
        });
    }

    public function email(): Factory
    {
        return $this->state(function (array $attributes) {
            $body = <<<'BODY'
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.

BODY;

            return [
                'type' => TypesEnum::Email,
                'meta_data' => MailDto::from([
                    'to' => 'info+12345@llmassistant.io',
                    'from' => 'foo@var.com',
                    'subject' => 'This is it',
                    'header' => 'This is header',
                    'body' => $body,
                ]),
            ];
        });
    }

    public function pptx(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TypesEnum::Pptx,
            ];
        });
    }
}
