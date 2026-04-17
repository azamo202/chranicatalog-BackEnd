<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SupportVideo extends Model
{
    use HasTranslations;

    protected $fillable = ['title', 'youtube_url'];
    public $translatable = ['title'];
}