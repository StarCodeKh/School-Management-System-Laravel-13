<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'status',
        'paid_on',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'status' => PayrollStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // net salary is always derived, never typed in
        static::saving(fn (Payroll $payroll) => $payroll->net_salary = max(0, (float) $payroll->basic_salary + (float) $payroll->allowances - (float) $payroll->deductions));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** "November 2026" */
    public function monthLabel(): string
    {
        return Carbon::createFromFormat('Y-m-d', $this->month.'-01')->format('F Y');
    }
}
