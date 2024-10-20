<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVariableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'addition_item_id',
        'addition_variable_id',
        'value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userVariable(): BelongsTo
    {
        return $this->belongsTo(UserVariable::class);
    }
}
