<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionVariableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'addition_item_id',
        'addition_variable_id',
        'value',
    ];

    public function additionItem(): BelongsTo
    {
        return $this->belongsTo(AdditionItem::class);
    }

    public function additionVariable(): BelongsTo
    {
        return $this->belongsTo(AdditionVariable::class);
    }
}
