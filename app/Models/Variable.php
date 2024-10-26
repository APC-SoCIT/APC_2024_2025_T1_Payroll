<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required',
        'min',
    ];

    public function userVariables(): HasMany
    {
        return $this->hasMany(UserVariable::class);
    }
}
