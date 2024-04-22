<?php 

namespace App\Domains\UnStructured;

use Spatie\LaravelData\Data;

class StructuredDto extends Data {

    public function __construct(
        public StructuredTypeEnum $type,
        public string $content, 
        public string $title, 
        public string $created_by, 
        public string $last_updated_by, 
        public string $page, 
        public string $guid,
        public string $file_name,
        public string $updated_at,
        public string $coordinates,
        public string $element_depth,
        public bool $is_continuation = false,
        public string|null $parent_id,
        public string|null $description,
        public string|null $subject,
        public string|null $keywords,
        public string|null $category,
    )
    {
        
    }
}