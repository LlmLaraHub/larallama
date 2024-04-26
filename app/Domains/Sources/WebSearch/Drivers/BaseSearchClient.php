<?php 

namespace App\Domains\Sources\WebSearch\Drivers;


use App\Domains\Sources\WebSearch\Response\SearchResponseDto;

abstract class BaseSearchClient  {
 
    
    public function search(string $search, array $options = []) : SearchResponseDto
    {
        return [];
    }
}