<?php 

namespace LlmLaraHub\TagFunction\Helpers;

use LlmLaraHub\TagFunction\Models\Tag;

trait Taggable {
    
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

