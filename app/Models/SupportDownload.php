<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SupportDownload extends Model
{
    use HasTranslations;

    protected $fillable = ['title', 'pdf_file_path'];
    public $translatable = ['title'];
}
