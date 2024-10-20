<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An addition within a payroll item
 **/
class AdditionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'addition_id',
        'amount',
    ];

    public function addition(): BelongsTo
    {
        return $this->belongsTo(Addition::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function additionVariableItems(): HasMany
    {
        return $this->hasMany(AdditionVariableItem::class);
    }
}
