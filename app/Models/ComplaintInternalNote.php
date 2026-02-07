<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintInternalNote extends Model
{
    protected $fillable = [
        'complaint_id',
        'author_id',
        'author_role',
        'note',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
