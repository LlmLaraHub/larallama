<?php

namespace App\Domains\Transformers\Dtos;

use Spatie\LaravelData\Data;

class ContactDto extends Data
{
    public function __construct(
        public ?string $email = '',
        public ?string $first_name = '',
        public ?string $last_name = '',
        public ?string $company_name = '',
        public ?string $phone = '',
        public array $socials = []
    ) {
    }
}
