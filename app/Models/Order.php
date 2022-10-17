<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'message',
        'image',
        'caption',
        'price',
        'send_date',
        'send_time',
        'status',
        'users_id',
        'type',
        'message_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'users_id',
        'type',
        'message_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'send_date'  => 'date',
        'send_time'  => 'time',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
