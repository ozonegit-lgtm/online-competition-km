<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'roles';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * อนุญาตให้ Mass Assignment
     */
    protected $fillable = [
        'role_name',
        'display_name',
        'description',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Role มี User ได้หลายคน
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}