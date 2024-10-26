<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A deduction within a payroll item
 **/
class ItemDeduction extends Model
{
    use HasFactory;

    protected $table = 'item_deductions';

    protected $fillable = [
        'payroll_item_id',
        'deduction_id',
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
