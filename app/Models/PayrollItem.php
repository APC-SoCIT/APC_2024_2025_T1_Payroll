<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An account's individual entry within a payroll period
 **/
class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cutoff_id',
        'amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cutoff(): BelongsTo
    {
        return $this->belongsTo(Cutoff::class);
    }

    public function itemAdditions(): HasMany
    {
        return $this->hasMany(ItemAddition::class);
    }

    public function itemDeductions(): HasMany
    {
        return $this->hasMany(ItemDeduction::class);
    }
}
