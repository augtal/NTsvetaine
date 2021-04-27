<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    #returns single value
    public function getLastestPrice(){
        return $this->hasOne(AdvertisementPrices::class)->orderBy('updated_at', 'DESC');
    }

    public function getPrices(){
        return $this->hasMany(AdvertisementPrices::class)->orderBy('updated_at', 'DESC');
    }

    public function getLocation(){
        return $this->hasOne(AdvertisementLocation::class, 'advertisement_id');
    }

    public function getDetails(){
        return $this->hasOne(AdvertisementDetails::class, 'advertisement_id');
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
