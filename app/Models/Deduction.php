<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A type/description of a deduction
 **/
class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'calculated',
    ];

    public function additionItems(): HasMany
    {
        return $this->hasMany(AdditionItem::class);
    }
}
