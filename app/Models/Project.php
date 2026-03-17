<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_code',
        'person_id',
        'type',
        'status',
        'lpo_url',
        'fixed_total_amount',
        'fixed_description',
        'invoice_interval_days',
        'withdrawal_reason',
    ];

    protected function casts(): array
    {
        return [
            'fixed_total_amount' => 'decimal:2',
        ];
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function company()
    {
        return $this->hasOneThrough(Company::class, Person::class, 'id', 'id', 'person_id', 'company_id');
    }

    public function professions()
    {
        return $this->hasMany(ProjectProfession::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Generate the next project code: PRJ-YYYY-MM-Seq
     */
    public static function generateCode(): string
    {
        $prefix = 'PRJ-' . now()->format('Y-m');
        $lastProject = static::where('project_code', 'LIKE', $prefix . '%')
            ->orderByDesc('project_code')
            ->first();

        $seq = 1;
        if ($lastProject) {
            $parts = explode('-', $lastProject->project_code);
            $seq = (int) end($parts) + 1;
        }

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
