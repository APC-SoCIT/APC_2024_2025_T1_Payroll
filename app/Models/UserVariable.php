<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required',
        'min',
    ];

    public function userVariableItems(): HasMany
    {
        return $this->hasMany(UserVariableItem::class);
    }
}
