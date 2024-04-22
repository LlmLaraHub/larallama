<?php 

namespace App\Domains\UnStructured;

use Spatie\LaravelData\Data;

enum StructuredTypeEnum : string {

    case Narrative = 'narrative';
    case Title = 'title';
    case Table = 'table';
    case TableRow = 'table_row';
    case Image = 'image';
    case Footer = 'footer';
}