<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProfession extends Model
{
    protected $fillable = [
        'project_id',
        'profession_name',
        'hourly_rate',
        'no_of_persons',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Computed: hourly_rate * no_of_persons
     */
    public function getTotalAttribute(): float
    {
        return round($this->hourly_rate * $this->no_of_persons, 2);
    }

    protected $appends = ['total'];
}
