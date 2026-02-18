<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'price',
        'old_price',
        'description',
        'image'
    ];

    protected $appends = ['image_url'];

    public function favorites()
    {
        return $this->hasMany(\App\Models\Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'favorites');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            // Если в поле image хранится уже полный URL
            return $this->image;
        }

        // Иначе строим URL из локального файла
        return Storage::url($this->image);
    }

//    'https://ir.ozone.ru/s3/multimedia-1-p/wc1000/7490917897.jpg';

// https://cdn.27.ua/sc--media--prod/default/53/9e/a5/539ea5fa-f1cd-4e2b-bb07-823702dc1b6c.jpg
}
