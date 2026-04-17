<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class MaintenanceCenter extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'phone', 'address', 'location_link'];
    public $translatable = ['name', 'address'];
}