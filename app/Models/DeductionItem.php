<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A deduction within a payroll item
**/
class DeductionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
    ];

    public function deduction(): BelongsTo
    {
        return $this->belongsTo(Deduction::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
