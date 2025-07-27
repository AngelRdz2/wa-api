<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'moratorium_classification_id',
        'template_name',
        'template',
        // otros campos que tengas
    ];

    public function classification()
    {
        return $this->belongsTo(MoratoriumClassification::class, 'moratorium_classification_id');
    }
}
