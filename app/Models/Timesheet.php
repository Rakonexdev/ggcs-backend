<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        'project_id',
        'date_logged',
        'profession_name',
        'rate_per_hour',
        'total_hours',
        'date_from',
        'date_to',
        'total_price',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_logged' => 'date',
            'date_from' => 'date',
            'date_to' => 'date',
            'rate_per_hour' => 'decimal:2',
            'total_hours' => 'decimal:2',
            'total_price' => 'decimal:2',
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
}
