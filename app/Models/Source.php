<?php

namespace App\Models;

use App\Domains\Sources\SourceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

class Source extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'type' => SourceTypeEnum::class,
    ];

    public function run(): void
    {
        $class = 'App\\Domains\\Sources\\' . $this->type->name;

        if(!class_exists($class)) {
            throw new \Exception("Source Class does not exist " . $class);
        }

        $class = app()->make($class);

        $class->handle($this);
    }

    public function collection() : BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
