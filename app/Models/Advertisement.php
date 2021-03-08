<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    public function lastestPrice(){
        return $this->hasOne(AdvertisementPrices::class)->orderBy('updated_at', 'DESC');
    }

    public function getCategory(){
        return $this->hasOne(AdvertCategories::class, 'id', 'category');
    }

    public function getType(){
        return $this->hasOne(AdvertTypes::class, 'id', 'type');
    }

    public function getWebsite(){
        return $this->hasOne(REWebsites::class, 'id', 'r_e_websites_id');
    }

    use HasFactory;
}
