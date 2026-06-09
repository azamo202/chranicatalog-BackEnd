<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SupportVideo extends Model
{
    use HasTranslations;

    protected $fillable = ['title', 'youtube_id'];
    public $translatable = ['title'];
    protected $appends = ['youtube_url'];

    public function getYoutubeUrlAttribute()
    {
        return $this->youtube_id ? 'https://www.youtube.com/watch?v=' . $this->youtube_id : null;
    }
}