<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';

    protected $fillable = [
        'name',
        'phone',
        'qatar_id',
        'id_expiration_date',
        'id_photo_url',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'id_expiration_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
