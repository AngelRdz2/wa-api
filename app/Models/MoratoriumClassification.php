<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoratoriumClassification extends Model
{
    protected $fillable = ['name'];

    public function templates()
    {
        return $this->hasMany(MessageTemplate::class, 'moratorium_classification_id');
    }
}
