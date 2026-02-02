<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
    ];

    // (Optional, tapi bagus) relasi ke complaints
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    // (Future-proof) relasi ke agents
    public function agents()
    {
        return $this->hasMany(User::class)
                    ->where('role', 'AGENT');
    }
}
