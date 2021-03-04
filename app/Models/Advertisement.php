<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    public function lastestPrice()
    {
        return $this->hasOne(AdvertisementPrices::class)->orderBy('updated_at', 'DESC');
    }

    use HasFactory;
}
