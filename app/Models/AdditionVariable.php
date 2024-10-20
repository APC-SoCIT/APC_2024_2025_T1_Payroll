<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdditionVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function additionVariableItems(): HasMany
    {
        return $this->hasMany(AdditionVariableItem::class);
    }
}
