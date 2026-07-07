<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    protected $fillable = ['admin_id', 'user_id', 'action', 'ip_address'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}