<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_code',
        'project_id',
        'issued_at',
        'total_amount',
        'file_url',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Computed: sum of all collections for this invoice
     */
    public function getPaidAmountAttribute(): float
    {
        return round($this->collections()->sum('amount'), 2);
    }

    /**
     * Computed: total_amount - paid_amount
     */
    public function getOutstandingAmountAttribute(): float
    {
        return round($this->total_amount - $this->paid_amount, 2);
    }

    protected $appends = ['paid_amount', 'outstanding_amount'];

    /**
     * Generate the next invoice code: Inv-YYYY-MM-Seq
     */
    public static function generateCode(): string
    {
        $prefix = 'Inv-' . now()->format('Y-m');
        $lastInvoice = static::where('invoice_code', 'LIKE', $prefix . '%')
            ->orderByDesc('invoice_code')
            ->first();

        $seq = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_code);
            $seq = (int) end($parts) + 1;
        }

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
