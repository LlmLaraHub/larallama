<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\UnStructured\StructuredDto;
use App\Domains\UnStructured\StructuredTypeEnum;

/**
 * @NOTE
 * the properies so far are things that might be shared page to page
 */
abstract class BaseTransformer
{
    public string $creator = '';

    public string $last_updated_by = '';

    public string $path_to_file = '';

    public mixed $updated_at = '';

    public string $coordinates = '';

    public string $keywords = '';

    public string $category = '';

    public string $description = '';

    public string $subject = '';

    public string $title = '';

    public function output(
        StructuredTypeEnum $type,
        string $content,
        mixed $page_number,
        mixed $guid,
        mixed $element_depth,
        bool $is_continuation = false,
    ): StructuredDto {

        return StructuredDto::from([
            'type' => $type,
            'content' => $content,
            'title' => $this->title,
            'created_by' => $this->creator,
            'last_updated_by' => $this->last_updated_by,
            'page' => $page_number,
            'guid' => $guid,
            'file_name' => $this->path_to_file,
            'updated_at' => $this->updated_at,
            'coordinates' => $this->coordinates,
            'element_depth' => $element_depth,
            'is_continuation' => $is_continuation,
            'description' => $this->description,
            'subject' => $this->subject,
            'keywords' => $this->keywords,
            'category' => $this->category,
            'parent_id' => null,
        ]);

    }

    public function getTitleFormatted(): string
    {
        return str($this->title)->snake()->toString();
    }
}
