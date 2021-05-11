<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationAdvertisements extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'advertisement_id'
    ];

    public $timestamps = false;
}
