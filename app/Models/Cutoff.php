<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Keeps track of the cutoff dates of a payroll period
 */
class Cutoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'cutoff_date',
        'end_date',
        'month_end',
    ];

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function hasEnded(): bool
    {
        return $this->end_date < Carbon::now()->toDateString();
    }
}
