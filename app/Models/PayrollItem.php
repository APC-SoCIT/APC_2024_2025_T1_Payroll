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
        'payroll_period_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function additionItems(): HasMany
    {
        return $this->hasMany(AdditionItem::class);
    }

    public function deductionItems(): HasMany
    {
        return $this->hasMany(DeductionItem::class);
    }
}
