<?php 

namespace LlmLaraHub\EmailSource;

use App\Models\Collection;

class GetEmail {
    

    public function handle(Collection $collection) {
        return $collection->email;
    }
};