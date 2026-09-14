<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const STATUSES = ['unread', 'read', 'archived'];

    protected $fillable = ['name', 'phone', 'email', 'message', 'status', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];
}
